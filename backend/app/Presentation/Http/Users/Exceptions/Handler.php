<?php

namespace App\Presentation\Http\Users\Exceptions;

use App\Infrastructure\Persistence\Eloquent\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = ['password', 'password_confirmation'];

    /**
     * Registers a custom exception handler for the application.
     *
     * This function integrates with Sentry to capture unhandled exceptions.
     * It utilizes Laravel's reportable method to define how exceptions
     * should be reported. Any unhandled exception will be captured and sent
     * to Sentry for monitoring and troubleshooting.
     */
    public function register(): void
    {
        $this->reportable(function (\Throwable $e) {
            // Only use Sentry if it's installed and configured
            if (class_exists(\Sentry\Laravel\Integration::class)) {
                \Sentry\Laravel\Integration::captureUnhandledException($e);
            }
        });
    }

    /**
     * Report or log an exception.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function report(\Throwable $exception)
    {
        // Log to CloudWatch or your logging service
        Log::error($this->getLoggableException($exception));

        parent::report($exception);
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->respondUnauthenticated('Unauthenticated. Please login again.');
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, \Throwable $e)
    {
        if ($e instanceof ValidationException) {
            return $this->validationError(
                $e->errors(),
                'Validation failed'
            );
        }

        if ($e instanceof AuthenticationException) {
            return $this->respondUnauthenticated('Unauthenticated. Please login again.');
        }

        if ($this->shouldManuallyHandleException($e)) {
            return $this->manuallyHandleException($e);
        }

        return parent::render($request, $e);
    }

    /**
     * Retrieves a loggable message from an exception.
     *
     * This function attempts to extract a loggable message from the given exception object.
     * It creates a JSON structure with exception details for better logging.
     *
     * @param \Throwable $exception the exception object to extract a loggable message from
     *
     * @return string the loggable message or class name of the exception
     */
    private function getLoggableException(\Throwable $exception): string
    {
        try {
            return json_encode([
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
            ]);
        } catch (\Exception $e) {
            // Fallback to basic message
            if (method_exists($exception, 'getMessage')) {
                return $exception->getMessage();
            }
            return class_basename($exception);
        }
    }

    /**
     * Check if the given exception should be manually handled.
     *
     * @param \Throwable $exception the exception to check
     *
     * @return bool returns true if the exception should be manually handled, false otherwise
     */
    private function shouldManuallyHandleException(\Throwable $exception): bool
    {
        $type = class_basename($exception);
        $exceptions = [
            'TokenMismatchException',
            'ThrottleRequestsException',
            'NotFoundHttpException'
        ];

        return in_array($type, $exceptions);
    }

    /**
     * Manually handle the given exception and return an appropriate JSON response.
     *
     * @param \Throwable $exception the exception to handle
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function manuallyHandleException(\Throwable $exception): \Illuminate\Http\JsonResponse
    {
        $type = class_basename($exception);

        switch ($type) {
            case 'TokenMismatchException':
                return response()->json([
                    'error' => 'Invalid CSRF token sent with request.'
                ], 419);

            case 'ThrottleRequestsException':
                return response()->json([
                    'error' => 'Too many requests.'
                ], 429);

            case 'NotFoundHttpException':
                return response()->json([
                    'error' => 'There is nothing to see here.'
                ], 404);

            default:
                return response()->json([
                    'error' => $exception->getMessage()
                ], 500);
        }
    }
}
