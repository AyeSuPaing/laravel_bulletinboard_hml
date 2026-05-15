<?php

namespace App\Services\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Models\Post;
use App\Constants\GeneralConst;
use App\Exports\Api\PostListExport;
use App\Imports\Api\PostListImport;
use App\Traits\ApiResponseTrait;

class PostService
{
    use ApiResponseTrait;

    /**
     * Get post list
     *
     * @param object $request
     * @return object
     */
    public function list(object $request): object
    {
        // Logic to fetch post list from database or any other source
        $query = Post::query();
        $query->with(['createdUser', 'updatedUser', 'deletedUser']);
        $query->orderBy('id', 'desc');

        // search
        if (isset($request['search_post_name'])
            && $request['search_post_name'] != '') {
            $query->where('title', 'like', '%' . addcslashes($request['search_post_name'], '%_\\') . '%');
        }

        if (isset($request['search_post_description'])
            && $request['search_post_description'] != '') {
            $query->where('description', 'like', '%' . addcslashes($request['search_post_description'], '%_\\') . '%');
        }

        if (isset($request['search_post_status'])
            && $request['search_post_status'] != '') {
            $query->where('status', $request['search_post_status']);
        }

        if (isset($request['search_created_from'])
            && $request['search_created_from'] != '') {
            $query->where('created_at', '>=', $request['search_created_from']);
        }

        if (isset($request['search_created_to'])
            && $request['search_created_to'] != '') {
            $query->where('created_at', '<=', $request['search_created_to']);
        }
        return $query->paginate(10);
    }

    /**
     * getPostById
     *
     * @param  mixed $id
     * @return Post
     */
    public function getPostById($id): Post
    {
        try {
            $post = Post::findOrFail($id);
            return $post;
        } catch (ModelNotFoundException $e) {
            throw new HttpResponseException(
                $this->error(
                    ["error" => "Post not found."],
                    JsonResponse::HTTP_BAD_REQUEST
                )
            );
        }
    }

    /**
     * save post
     *
     * @param  Request $request
     * @param  $id
     * @return Post
     */
    public function save(Request $request, $id = null): Post
    {
        DB::beginTransaction();
        try {
            $post = $id ? Post::findOrFail($id) : new Post();
            $data = [
                "title" => $request['title'],
                "description" => $request['description'],
                "status" => $request['status'] ?? GeneralConst::ACTIVE,
                "created_user_id" => $id ? $post['created_user_id'] : auth('sanctum')->user()->id,
                "updated_user_id" => auth('sanctum')->user()->id,
            ];
            $post->fill($data)->save();
            DB::commit();
            return $post;
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            if ($e instanceof ModelNotFoundException) {
                throw new HttpResponseException(
                    $this->error(
                        ["error" => "Post not found."],
                        JsonResponse::HTTP_BAD_REQUEST
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
     * delete posts
     *
     * @param  Request $request
     * @return array
     */
    public function delete(Request $request): array
    {
        DB::beginTransaction();
        try {
            $query = Post::query();
            $query->whereIn('id', $request['deleted_post_ids']);
            $query->update(['deleted_user_id' => auth('sanctum')->user()->id]);
            $query->delete();
            DB::commit();
            return ["message" => "Deleted Posts Successfully."];
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
     * download
     *
     * @param  mixed $request
     * @return void
     */
    public function download(Request $request)
    {
        try {
            $posts = Post::whereIn('id', $request['download_post_ids'])->get();
            return Excel::download(new PostListExport($posts), 'posts.xlsx');
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

    /**
     * upload
     *
     * @param  mixed $request
     * @return array
     */
    public function upload(Request $request): array
    {
        DB::beginTransaction();
        try {
            $import = new PostListImport();
            Excel::import($import, $request['excel_file']);
            DB::commit();
            return ["message" => "Uploaded Posts Successfully."];
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            if ($e instanceof ValidationException) {
                $failures = $e->failures();
                $customErrMsg = collect($failures)->map(function ($failure) {
                    return "Row " . $failure->row() . ": " . $failure->errors()[0];
                })->all();
                throw new HttpResponseException(
                    $this->error(
                        $customErrMsg,
                        JsonResponse::HTTP_UNPROCESSABLE_ENTITY
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
}
