<?php

namespace App\Http\Controllers\Api;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PostDeleteRequest;
use App\Http\Requests\Api\PostDownloadRequest;
use App\Http\Requests\Api\PostSaveRequest;
use App\Http\Requests\Api\PostSearchRequest;
use App\Http\Requests\Api\PostUploadRequest;
use App\Http\Resources\Api\PostResource;
use App\Services\Api\PostService;
use App\Traits\ApiResponseTrait;

class PostController extends Controller
{
    use ApiResponseTrait;

    /**
     * Post service instance
     *
     * @var \App\Services\PostService
     */
    protected $post_service;

    /**
     * auth_user
     *
     * @var mixed
     */
    protected $auth_user;

    /**
     * PostController constructor.
     */
    public function __construct()
    {
        $this->post_service = new PostService();
        $this->auth_user = auth('sanctum')->user();
        if (!$this->auth_user) {
            return $this->error(["Unauthorized."], JsonResponse::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * getPostList
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function getPostList(PostSearchRequest $request): JsonResponse
    {
        // Fetch post list using the post service
        $posts = $this->post_service->list($request);
        $response = PostResource::collection($posts);
        return $this->paginate($response);
    }

    /**
     * getPost
     *
     * @param  mixed $id
     * @return JsonResponse
     */
    public function getPost($id): JsonResponse
    {
        $post = $this->post_service->getPostById($id);
        $response = new PostResource($post);
        return $this->success($response->resolve());
    }

    /**
     * createPost
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function createPost(PostSaveRequest $request): JsonResponse
    {
        $post = $this->post_service->save($request);
        $response = new PostResource($post);
        return $this->success($response->resolve());
    }

    /**
     * updatePost
     *
     * @param  mixed $request
     * @param  mixed $id
     * @return JsonResponse
     */
    public function updatePost(PostSaveRequest $request, $id = null): JsonResponse
    {
        $post = $this->post_service->save($request, $id);
        $response = new PostResource($post);
        return $this->success($response->resolve());
    }

    /**
     * upload Posts
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function uploadPosts(PostUploadRequest $request): JsonResponse
    {
        $success_msg = $this->post_service->upload($request);
        return $this->success($success_msg);
    }

    /**
     * download Posts
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function downloadPosts(PostDownloadRequest $request)
    {
        return $this->post_service->download($request);
    }

    /**
     * deletePosts
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function deletePosts(PostDeleteRequest $request): JsonResponse
    {
        $success_msg = $this->post_service->delete($request);
        return $this->success($success_msg);
    }

    /**
     * downloadTemplate
     *
     * @return BinaryFileResponse
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        $filename = 'posts_template.xlsx';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ];

        // Get path from config directory
        $path = config_path('template/' . $filename);

        // Download file with custom headers
        return response()->download($path, $filename, $headers);
    }
}
