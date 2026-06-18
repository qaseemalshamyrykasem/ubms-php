<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;

class AuditLogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Log write/delete actions only
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) && $request->user()) {
            AuditLog::record('api.' . strtolower($request->method()), null, null, [
                'url' => $request->path(),
                'input' => $request->except(['password', 'password_confirmation', 'token']),
            ]);
        }

        return $response;
    }
}
