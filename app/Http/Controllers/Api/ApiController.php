<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /** \Transformers\Transformer
     *
     * @var null
     */
    protected $transformer = null;

    /**
     * Return generic json response with the given data.
     *
     * @param $data
     * @param int $statusCode
     * @param array $headers
     * @return JsonResponse
     */
    protected function respond($data, int $statusCode = 200, array $headers = []): JsonResponse
    {
        return response()->json($data, $statusCode, $headers);
    }

    /**
     * @param null $data
     * @return JsonResponse
     */
    protected function respondSuccess($data = null): JsonResponse
    {
        return $this->respond($data);
    }

    /**
     * Respond with created.
     *
     * @param $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondCreated($data): JsonResponse
    {
        return $this->respond($data, 201);
    }

    /**
     * Respond with no content.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondNoContent(): JsonResponse
    {
        return $this->respond(null, 204);
    }

    /**
     * Respond with error.
     *
     * @param string $message
     * @param $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondError(string $message, int $statusCode = 500): JsonResponse
    {
        return $this->respond([
            'error' => [
                'message' => $message,
                'status_code' => $statusCode
            ]
        ], $statusCode);
    }
    /**
     * Respond with unauthorized.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondUnauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->respondError($message, 401);
    }

    /**
     * Respond with forbidden.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondForbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->respondError($message, 403);
    }

    /**
     * Respond with not found.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondNotFound(string $message = 'Not Found'): JsonResponse
    {
        return $this->respondError($message, 404);
    }

    /**
     * Respond with failed login.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondFailedLogin(): JsonResponse
    {
        return $this->respond([
            'error' => [
                'email or password' => 'is invalid',
            ]
        ], 422);
    }

    /**
     * Respond with internal error.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondInternalError(string $message = 'Internal Error'): JsonResponse
    {
        return $this->respondError($message, 500);
    }

    /**
     * @param string $route
     * @param Request $request
     * @param array $payload
     * @return string
     */
    protected function objectUrl(string $route, Request $request, array $payload): string
    {
        $dataRaw = array_merge($request->data, $payload);

        return sprintf(
            '%s?data=%s',
            route($route),
            encrypt($dataRaw, $this->salt, $this->key)
        );
    }

}
