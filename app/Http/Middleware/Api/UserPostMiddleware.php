<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Constants\GeneralConst;
use App\Models\Post;
use App\Traits\ApiResponseTrait;

class UserPostMiddleware
{
    use ApiResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $post_id = $request->route('id') ?? null;
        $deleted_post_ids = $request['deleted_post_ids'] ?? [];
        $user_id = auth('sanctum')->user()->id;
        $user_role = auth('sanctum')->user()->role;
        if ($user_role == GeneralConst::USER) {
            if ($post_id) {
                $post_check = Post::where('id', $post_id)
                    ->where('created_user_id', $user_id)
                    ->exists();
                if (!$post_check) {
                    return $this->error(["error" => "Do not have permission to access this post."], JsonResponse::HTTP_BAD_REQUEST);
                }
            }

            if ($deleted_post_ids) {
                $post_check = Post::whereIn('id', $deleted_post_ids)
                    ->where('created_user_id', $user_id)
                    ->count();
                if (count($deleted_post_ids) != $post_check) {
                    return $this->error(["error" => "Do not have permission to access some posts."], JsonResponse::HTTP_BAD_REQUEST);
                }
            }
        }
        return $next($request);
    }
}
