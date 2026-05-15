<?php

namespace App\Services\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportException;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Mail\UserMail;
use App\Constants\GeneralConst;

class UserService
{
    use ApiResponseTrait;

    /**
     * Get user list
     *
     * @param  mixed $request
     * @return object
     */
    public function list(object $request): object
    {
        // Logic to fetch user list from database or any other source
        $query = User::query();
        $query->with(['createdUser', 'updatedUser', 'deletedUser']);
        $query->orderBy('id', 'desc');

        // search
        if (isset($request['search_name'])) {
            $query->where('name', 'like', '%' . addcslashes($request['search_name'], '%_\\') . '%');
        }

        if (isset($request['search_email'])) {
            $query->where('email', 'like', '%' . addcslashes($request['search_email'], '%_\\') . '%');
        }

        if (isset($request['search_role'])) {
            $query->where('role', $request['search_role']);
        }

        if (isset($request['search_created_from'])) {
            $query->where('created_at', '>=', $request['search_created_from']);
        }

        if (isset($request['search_created_to'])) {
            $query->where('created_at', '<=', $request['search_created_to']);
        }
        return $query->paginate(10);
    }

    /**
     * getUserById
     *
     * @param  mixed $id
     * @return User
     */
    public function getUserById($id): User
    {
        try {
            $user = User::findOrFail($id);
            return $user;
        } catch (ModelNotFoundException $e) {
            throw new HttpResponseException(
                $this->error(
                    ["error" => "User not found."],
                    JsonResponse::HTTP_BAD_REQUEST
                )
            );
        }
    }

    /**
     * save user
     *
     * @param  Request $request
     * @param  $id
     * @return User
     */
    public function save(Request $request, $id = null): User
    {
        DB::beginTransaction();
        try {
            $user = $id ? User::findOrFail($id) : new User();
            $email_changed = $id && $user->email !== $request['email'];
            $data = [
                "name" => $request['name'],
                "email" => $request['email'],
                "phone" => $request['phone'],
                "address" => $request['address'],
                "dob" => $request['dob'],
                "created_user_id" => $id ? $user['created_user_id'] : auth('sanctum')->user()->id,
                "updated_user_id" => auth('sanctum')->user()->id
            ];

            if (isset($request['role']) && auth('sanctum')->user()->role == GeneralConst::ADMIN) {
                $data['role'] = $request['role'];
            }

            if ($request->has('password')) {
                $data['password'] = Hash::make($request['password']);
            }
            $user->fill($data)->save();
            // clear lock in initial
            if (!$id) {
                $request->merge([
                    'lock_flg' => false,
                    'login_fail_count' => 0
                ]);
                $this->clearLoginAttempts($request);
            }
            // profile
            if ($request->hasFile('profile')) {
                $path = $this->saveProfile($request, $user['profile_path']);
                $user->profile_path = $path;
                $user->save();
            }
            // update auth
            if (auth('sanctum')->user()->id === $user['id']) {
                auth('sanctum')->setUser($user);
            }
            if (!$id || $email_changed) {
                Mail::to($user->email)->send(new UserMail($user, $request['password'], $id ? true : false));
            }
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            if ($e instanceof ModelNotFoundException) {
                throw new HttpResponseException(
                    $this->error(
                        ["error" => "User not found."],
                        JsonResponse::HTTP_BAD_REQUEST
                    )
                );
            }

            if ($e instanceof TransportException) {
                throw new HttpResponseException(
                    $this->error(
                        ["error" => "Mail Transportation is failed!"],
                        JsonResponse::HTTP_NETWORK_AUTHENTICATION_REQUIRED
                    )
                );
            }

            throw new HttpResponseException(
                $this->error(
                    ["error" => "Something wrong."],
                    JsonResponse::HTTP_INTERNAL_SERVER_ERROR
                )
            );
        }
    }

    /**
     * saveProfile
     *
     * @param  mixed $request
     * @param  mixed $old_path
     * @return string
     */
    private function saveProfile(Request $request, ?string $old_path): string
    {
        if ($old_path && Storage::disk('public')->exists($old_path)) {
            Storage::disk('public')->delete($old_path);
        }
        $profile_name = time() . '.' . $request['profile']->extension();
        $path = "profile";
        $profile_path = Storage::disk('public')->putFileAs($path, $request['profile'], $profile_name);
        return $profile_path;
    }

    /**
     * unlock users
     *
     * @param  Request $request
     * @return array
     */
    public function unlock(Request $request): array
    {
        if (array_search(auth('sanctum')->user()->id, $request['unlocked_user_ids']) !== false) {
            throw new HttpResponseException(
                $this->error(
                    ["error" => "Login User do not unlocked."],
                    JsonResponse::HTTP_BAD_REQUEST
                )
            );
        }

        DB::beginTransaction();
        try {
            $query = User::query();
            $query->whereIn('id', $request['unlocked_user_ids']);
            $query->update(['lock_flg' => false, 'login_fail_count' => 0]);
            $this->unlockUsers($request, $request['unlocked_user_ids']);
            DB::commit();
            return ["message" => "Unlocked Users Successfully."];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            throw new HttpResponseException(
                $this->error(
                    ["error" => "Something wrong."],
                    JsonResponse::HTTP_INTERNAL_SERVER_ERROR
                )
            );
        }
    }

    /**
     * delete users
     *
     * @param  Request $request
     * @return array
     */
    public function delete(Request $request): array
    {
        if (array_search(auth('sanctum')->user()->id, $request['deleted_user_ids']) !== false) {
            throw new HttpResponseException(
                $this->error(
                    ["error" => "Login User do not deleted."],
                    JsonResponse::HTTP_BAD_REQUEST
                )
            );
        }

        DB::beginTransaction();
        try {
            $query = User::query();
            $query->whereIn('id', $request['deleted_user_ids']);
            $query->update(['deleted_user_id' => auth('sanctum')->user()->id]);
            $query->delete();
            $this->unlockUsers($request, $request['deleted_user_ids']);
            DB::commit();
            return ["message" => "Deleted Users Successfully."];
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            throw new HttpResponseException(
                $this->error(
                    ["error" => "Something wrong."],
                    JsonResponse::HTTP_INTERNAL_SERVER_ERROR
                )
            );
        }
    }

    /**
     * Get the throttle key for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function throttleKey(Request $request): string
    {
        return Str::lower($request['email']);
    }

    /**
     * clear login attempts
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function clearLoginAttempts(Request $request)
    {
        User::where('email', $request['email'])
            ->update([
                'login_fail_count' => 0
            ]);

        RateLimiter::clear($this->throttleKey($request));
    }

    /**
     * unlock users
     *
     * @param  mixed $request
     * @return void
     */
    private function unlockUsers(Request $request, array $user_ids)
    {
        $admin_account_emails = User::whereIn('id', $user_ids)->withTrashed()->pluck('email');
        foreach ($admin_account_emails as $email) {
            $request->merge(['email' => $email]);
            // clear login attempts
            $this->clearLoginAttempts($request);
        }
    }
}
