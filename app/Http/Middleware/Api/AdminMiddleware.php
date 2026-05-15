<?php

namespace App\Http\Middleware\Api;

use Closure;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Constants\GeneralConst;
use App\Traits\ApiResponseTrait;

class AdminMiddleware
{
    use ApiResponseTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            !Auth::guard('sanctum')->check()
            || Auth::guard('sanctum')->user()->role !== GeneralConst::ADMIN
        ) {
            return $this->error(["error" => "Unauthorized"], JsonResponse::HTTP_UNAUTHORIZED);
        }
        return $next($request);
    }
}
