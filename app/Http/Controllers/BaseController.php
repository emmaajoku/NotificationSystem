<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * @param $message
     * @param int $status
     * @return JsonResponse
     */
    public function errorResponse($message, $status = 500): JsonResponse
    {
        $payload = [
            'status' => 'error',
            'message' => $message
        ];
        return new JsonResponse($payload, $status);
    }

    /**
     * @param $message
     * @param int $status
     * @return JsonResponse
     */
    public function successResponse($message, $status = 200): JsonResponse
    {

        $payload = [
            'status' => 'success',
            'data' => $message
        ];
        return new JsonResponse($payload, $status);
    }

    /**
     * @param $message
     * @param int $status
     * @return JsonResponse
     */
    public function createSuccessResponse($message, $status = 201): JsonResponse
    {
        $payload = [
            'status' => 'success',
            'data' => $message
        ];
        return new JsonResponse($payload, $status);
    }

    /**
     * @param \Exception $exception
     * @return JsonResponse
     */
    public function exceptionResponse(\Exception $exception): JsonResponse
    {
        $payload = [
            'status' => 'error',
            'message' => $exception->getMessage()
        ];
        return new JsonResponse($payload, 500);
    }
}
