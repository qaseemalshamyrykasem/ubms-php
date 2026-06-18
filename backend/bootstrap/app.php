<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\{
    HttpException, NotFoundHttpException, AccessDeniedHttpException, MethodNotAllowedHttpException
};

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            require __DIR__ . '/../routes/mobile.php';
        },
    )
    ->withCommands()
    ->withMiddleware(function (Middleware $middleware) {
        // Sanctum v4 doesn't need EnsureFrontendRequestsAreStateful
        // Trust all proxies (useful behind load balancers / shared hosting)
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'audit.log' => \App\Http\Middleware\AuditLogMiddleware::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);

        // Skip trimming/empty-conversion for file payloads
        $middleware->skipWhen(function (Request $request) {
            return $request->is('api/*') && $request->hasFile('*');
        }, [
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return handleApiException($e);
            }
            return null;
        });
    })
    ->create();

function handleApiException(Throwable $e)
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
    if ($e instanceof \DomainException) {
        return response()->json(['message' => $e->getMessage()], 422);
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
    return response()->json(['message' => 'حدث خطأ في الخادم. يرجى المحاولة لاحقاً.'], 500);
}
