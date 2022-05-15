<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponses
{
    /**
     * Success Response.
     *
     * @param   $data
     * @param  int  $statusCode
     * @return JsonResponse
     */
    public function successResponse($data, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse($data, $statusCode);
    }

    /**
     * Error Response.
     *
     * @param   $data
     * @param  string  $message
     * @param  int  $statusCode
     * @return JsonResponse
     */
    public function errorResponse($data, string $message = '', int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        if (!$message) {
            $message = Response::$statusTexts[$statusCode];
        }

        $data = [
            'message' => $message,
            'errors' => $data,
        ];

        return new JsonResponse($data, $statusCode);
    }

    /**
     * Response with status code 200.
     *
     * @param   $data
     * @return JsonResponse
     */
    public function okResponse($data): JsonResponse
    {
        return $this->successResponse($data);
    }

    /**
     * Response with status code 201.
     *
     * @param   $data
     * @return JsonResponse
     */
    public function createdResponse($data): JsonResponse
    {
        return $this->successResponse($data, Response::HTTP_CREATED);
    }

    /**
     * Response with status code 204.
     *
     * @return JsonResponse
     */
    public function noContentResponse(): JsonResponse
    {
        return $this->successResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Response with status code 400.
     *
     * @param   $data
     * @param  string  $message
     * @return JsonResponse
     */
    public function badRequestResponse($data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Response with status code 401.
     *
     * @param   $data
     * @param  string  $message
     * @return JsonResponse
     */
    public function unauthorizedResponse($data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Response with status code 403.
     *
     * @param   $data
     * @param  string  $message
     * @return JsonResponse
     */
    public function forbiddenResponse($data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Response with status code 404.
     *
     * @param   $data
     * @param  string  $message
     * @return JsonResponse
     */
    public function notFoundResponse($data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Response with status code 409.
     *
     * @param   $data
     * @param  string  $message
     * @return JsonResponse
     */
    public function conflictResponse($data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_CONFLICT);
    }

    /**
     * Response with status code 422.
     *
     * @param   $data
     * @param  string  $message
     * @return JsonResponse
     */
    public function unprocessableResponse($data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}