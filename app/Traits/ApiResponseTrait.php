<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Http\JsonResponse as HttpJsonResponse;

trait ApiResponseTrait
{
    /**
     * success
     *
     * @param  mixed $data
     * @return JsonResponse
     */
    protected function success(array $data): JsonResponse
    {
        return response()->json($data, HttpJsonResponse::HTTP_OK);
    }

    /**
     * paginate
     *
     * @param  mixed $resource
     * @return JsonResponse
     */
    protected function paginate(object $resource): JsonResponse
    {
        return response()->json([
            'data' => $resource->items(),
            'links' => [
                'first' => $resource->url(1),
                'last' => $resource->url($resource->lastPage()),
                'prev' => $resource->previousPageUrl(),
                'next' => $resource->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $resource->currentPage(),
                'last_page' => $resource->lastPage(),
                'per_page' => $resource->perPage(),
                'total' => $resource->total(),
            ],
        ], HttpJsonResponse::HTTP_OK);
    }

    /**
     * error
     *
     * @param  mixed $errors
     * @param  mixed $code
     * @return JsonResponse
     */
    protected function error(array $errors, int $code = 400): JsonResponse
    {
        return response()->json($errors, $code);
    }
}
