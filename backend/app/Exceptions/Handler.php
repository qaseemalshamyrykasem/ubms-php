<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\{
    HttpException, NotFoundHttpException, AccessDeniedHttpException, MethodNotAllowedHttpException
};
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->handleApiException($e);
        }
        return parent::render($request, $e);
    }

    private function handleApiException(Throwable $e)
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => 'بيانات غير صالحة',
                'errors' => $e->errors(),
            ], 422);
        }
        if ($e instanceof ModelNotFoundException) {
            return response()->json(['message' => 'المورد غير موجود'], 404);
        }
        if ($e instanceof AccessDeniedHttpException) {
            return response()->json(['message' => 'غير مصرّح لك بهذا الإجراء'], 403);
        }
        if ($e instanceof NotFoundHttpException) {
            return response()->json(['message' => 'الرابط غير موجود'], 404);
        }
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json(['message' => 'طريقة الطلب غير مدعومة'], 405);
        }
        if ($e instanceof HttpException) {
            return response()->json(['message' => $e->getMessage() ?: 'حدث خطأ'], $e->getStatusCode());
        }
        if (app()->environment('local', 'development')) {
            return response()->json([
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
        return response()->json(['message' => 'حدث خطأ في الخادم.'], 500);
    }
}
