<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;

class LogAudit
{
    // Mencatat setiap request yang mengubah data (POST/PUT/PATCH/DELETE) ke tabel audit_logs
    // untuk kebutuhan Audit Trail Pelanggan & Admin (fitur Superadmin C.2)
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->user() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'role' => $request->user()->getRoleNames()->first(),
                'action' => $request->method() . ' ' . $request->path(),
                'meta' => $request->except(['password', 'password_confirmation']),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }
}
