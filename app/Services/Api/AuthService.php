<?php

namespace App\Services\Api;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;
use App\Models\User;
use App\Constants\GeneralConst;
use App\Mail\PasswordResetMail;
use App\Traits\ApiResponseTrait;

class AuthService
{
    use ApiResponseTrait;

    protected $maxAttempts = 5;
    protected $decayMinutes = 24 * 60;

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
     * Determine if the user has too many failed login attempts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function hasTooManyLoginAttempts(Request $request): bool
    {
        return RateLimiter::tooManyAttempts(
            $this->throttleKey($request),
            $this->maxAttempts
        );
    }

    /**
     * Fire an event when a lockout occurs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function fireLockoutEvent(Request $request)
    {
        event(new Lockout($request));
    }

    /**
     * Increment the login attempts for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function incrementLoginAttempts(Request $request)
    {
        User::where('email', $request['email'])
            ->update([
                'login_fail_count' => DB::raw('login_fail_count + 1')
            ]);

        RateLimiter::hit(
            $this->throttleKey($request),
            $this->decayMinutes * 60
        );
    }

    /**
     * Clear the login locks for the given user credentials.
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
     * Login
     *
     * @param  mixed $request
     * @return array
     */
    public function login(object $request): array
    {
        DB::beginTransaction();
        try {
            // check too many
            if ($this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);

                $seconds = RateLimiter::availableIn($this->throttleKey($request));
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $timeMessage = '';
                if ($hours > 0) {
                    $timeMessage .= $hours . ' hour' . ($hours > 1 ? 's' : '');
                }
                if ($minutes > 0) {
                    if ($timeMessage !== '') {
                        $timeMessage .= ' ';
                    }

                    $timeMessage .= $minutes . ' minute' . ($minutes > 1 ? 's' : '');
                }

                throw new HttpResponseException(
                    $this->error(
                        [ "error" => "Too many login attempts. Please try again in {$timeMessage}." ],
                        JsonResponse::HTTP_TOO_MANY_REQUESTS
                    )
                );
            }
            $user = User::where('email', $request->email)->first();

            if (! $user) {
                $this->incrementLoginAttempts($request);
                DB::commit();
                throw new HttpResponseException(
                    $this->error(
                        ["email" => "Invalid Email"],
                        JsonResponse::HTTP_BAD_REQUEST
                    )
                );
            }

            if ($user && $user->lock_flg) {
                if (Carbon::parse($user->last_lock_at)->addDay()->isFuture()) {
                    throw new Exception("Account locked");
                }

                $user->update([
                    'lock_flg' => false,
                    'login_fail_count' => 0
                ]);
            }

            if (! Hash::check($request->password, $user->password)) {

                $this->incrementLoginAttempts($request);

                // check AFTER increment
                if ($this->hasTooManyLoginAttempts($request)) {

                    $user->update([
                        'lock_flg' => true,
                        'last_lock_at' => Carbon::now()
                    ]);

                    $this->fireLockoutEvent($request);

                    $seconds = RateLimiter::availableIn($this->throttleKey($request));

                    $hours = floor($seconds / 3600);
                    $minutes = floor(($seconds % 3600) / 60);

                    $timeMessage = '';

                    if ($hours > 0) {
                        $timeMessage .= $hours . ' hour' . ($hours > 1 ? 's' : '');
                    }

                    if ($minutes > 0) {
                        if ($timeMessage !== '') {
                            $timeMessage .= ' ';
                        }

                        $timeMessage .= $minutes . ' minute' . ($minutes > 1 ? 's' : '');
                    }

                    DB::commit();

                    throw new HttpResponseException(
                        $this->error(
                            [
                                "error" => "Too many login attempts. Please try again in {$timeMessage}."
                            ],
                            JsonResponse::HTTP_TOO_MANY_REQUESTS
                        )
                    );
                }

                DB::commit();

                throw new HttpResponseException(
                    $this->error(
                        ["password" => "Incorrect Password"],
                        JsonResponse::HTTP_BAD_REQUEST
                    )
                );
            }

            $user->last_login_at = Carbon::now();
            $user->tokens()->delete();
            $expiration = config('sanctum.expiration');
            $expires_at = now()->addMinutes($expiration);
            $token_res = $user->createToken(
                $request->email,
                ['*'],
                $expires_at
            );
            $token = $token_res->plainTextToken;
            // clear lock
            $this->clearLoginAttempts($request);
            auth('sanctum')->setUser($user);
            $user->save();
            DB::commit();
            return  [
                "access_token" => $token,
                "token_type" => "Bearer",
                "expires_at" => $expires_at->toDateTimeString(),
                "user_role" => GeneralConst::ROLES[$user->role],
                "user_id" => $user->id,
                "user_name" => $user->name,
                "last_login_at" => $user->last_login_at
            ];
        } catch (HttpResponseException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
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
     * logout
     *
     * @param  mixed $request
     * @return array
     */
    public function logout(object $request): array
    {
        DB::beginTransaction();
        try {
            $user = $request->user();
            if ($user) {
                $user->currentAccessToken()->delete();
                $user->tokens()->delete();
                DB::commit();
                return ["message" => "Logout Successfully!"];
            }
            throw new HttpResponseException(
                $this->error(
                    ["error" => "Token not found."],
                    JsonResponse::HTTP_BAD_REQUEST
                )
            );
        } catch (Exception $e) {
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
     * reset Password
     *
     * @param  mixed $request
     * @return array
     */
    public function resetPasswordMail(Request $request): array
    {
        DB::beginTransaction();
        try {
            $user = User::where('email', $request['email'])->first();
            if (!$user) {
                throw new HttpResponseException(
                    $this->error(
                        ["error" => "Email not found!"],
                        JsonResponse::HTTP_BAD_REQUEST
                    )
                );
            }
            // Send email
            $token = Str::random(64);
            DB::table('password_resets')->updateOrInsert(
                ['email' => $request['email']],
                ['token' => $token, 'created_at' => Carbon::now()]
            );
            $redirect_link = $request['reset_page_url'] . "?email=". urlencode($request['email']) . "&token=" . $token;
            Mail::to($user['email'])->send(new PasswordResetMail($user, $redirect_link));
            DB::commit();
            return ["message" => "Send password mail successfully."];
        } catch (Exception $e) {
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
     * updatePassword
     *
     * @param  mixed $request
     * @return bool
     */
    public function updatePassword(Request $request): bool
    {
        DB::beginTransaction();
        try {
            $query = User::query();
            $query->where('email', $request['email']);
            $query->update(['password' => Hash::make($request['password'])]);
            $data = $query->first();
            if (auth('sanctum')->user()?->id === $data['id']) {
                auth('sanctum')->setUser($data);
            }
            DB::table('password_resets')->where('email', $request['email'])->delete();
            DB::commit();
            return true;
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
     * getAuthSession
     *
     * @return array
     */
    public function getAuthSession(Request $request): array
    {
        $user = auth('sanctum')->user();
        $token = $request->user()->currentAccessToken();
        if (!$user) {
            throw new HttpResponseException(
                $this->error(
                    ["error" => "Unauthorized."],
                    JsonResponse::HTTP_UNAUTHORIZED
                )
            );
        }
        $user = User::find($user->id);
        auth('sanctum')->setUser($user);
        return [
            "access_token" => $request->bearerToken(),
            "token_type" => "Bearer",
            "expires_at" => $token->expires_at->toDateTimeString(),
            "user_role" => GeneralConst::ROLES[$user->role],
            "user_id" => $user->id,
            "user_name" => $user->name,
            "last_login_at" => $user->last_login_at
        ];
    }
    
    /**
     * getTableConfigs
     *
     * @return Collection
     */
    public function getTableConfigs(): Collection
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            throw new HttpResponseException(
                $this->error(
                    ["error" => "Unauthorized."],
                    JsonResponse::HTTP_UNAUTHORIZED
                )
            );
        }

        try {
            $databaseName = DB::getDatabaseName();
            $columns = DB::table('information_schema.columns')
                ->where('TABLE_SCHEMA', $databaseName)
                ->select([
                    "TABLE_NAME as table_name",
                    "COLUMN_NAME as column_name",
                    "DATA_TYPE as data_type",
                    "IS_NULLABLE as is_nullable",
                    "COLUMN_DEFAULT as column_default",
                    "CHARACTER_MAXIMUM_LENGTH as character_maximum_length",
                    "COLUMN_COMMENT as column_comment"
                ])
                ->whereNotIn('table_name', GeneralConst::IGNORED_TABLES)
                ->orderBy('table_name')
                ->get()
                ->groupBy('table_name');
            return $columns;
        } catch (\Exception $e) {
            Log::error($e);
            throw new HttpResponseException(
                $this->error(
                    ["error" => "Something wrong."],
                    JsonResponse::HTTP_INTERNAL_SERVER_ERROR
                )
            );
        }
    }
}
