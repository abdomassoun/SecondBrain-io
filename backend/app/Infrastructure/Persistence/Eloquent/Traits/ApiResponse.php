<?php

namespace App\Infrastructure\Persistence\Eloquent\Traits;

use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    protected function success($data = null, string $message = 'Success', int $status = Response::HTTP_OK)
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    protected function error(string $message = 'Error', int $status = Response::HTTP_BAD_REQUEST, $errors = null)
    {
        $response = [
            'status'  => false,
            'message' => $message,
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        Log::error('API Error: ' . $message, ['errors' => $errors]);

        return response()->json($response, $status);
    }
    protected function paginatedSuccess($paginator, string $message = 'Success')
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ], Response::HTTP_OK);
    }

    protected function unauthorized(string $message = 'Unauthorized')
    {
        return $this->error($message, Response::HTTP_UNAUTHORIZED);
    }

    protected function respondUnauthenticated(string $message = 'Unauthenticated')
    {
        return $this->error($message, Response::HTTP_FORBIDDEN);
    }

    protected function notFound(string $message = 'Resource not found')
    {
        return $this->error($message, Response::HTTP_NOT_FOUND);
    }

    protected function validationError(array $errors, string $message = 'Validation Error')
    {
        return $this->error($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    protected function created($data = null, string $message = 'Created successfully')
    {
        return $this->success($data, $message, Response::HTTP_CREATED);
    }

    protected function noContent()
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Response for successful update operations
     */
    protected function updated($data = null, string $message = 'Updated successfully')
    {
        return $this->success($data, $message, Response::HTTP_OK);
    }

    /**
     * Response for successful delete operations
     */
    protected function deleted(string $message = 'Deleted successfully')
    {
        return $this->success(null, $message, Response::HTTP_OK);
        // OR use noContent() for 204 - both are RESTful
        // return $this->noContent();
    }

    /**
     * Response for conflict errors (e.g., duplicate entries)
     */
    protected function conflict(string $message = 'Resource conflict', $errors = null)
    {
        return $this->error($message, Response::HTTP_CONFLICT, $errors);
    }

    /**
     * Response for server errors
     */
    protected function serverError(string $message = 'Internal server error', $errors = null)
    {
        Log::error('Server Error: ' . $message, ['errors' => $errors]);
        return $this->error($message, Response::HTTP_INTERNAL_SERVER_ERROR, $errors);
    }

    /**
     * Response for forbidden access
     */
    protected function forbidden(string $message = 'Forbidden')
    {
        return $this->error($message, Response::HTTP_FORBIDDEN);
    }

}
