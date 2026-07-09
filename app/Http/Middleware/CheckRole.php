<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    // Dipakai di routes: ->middleware('role:admin,superadmin')
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user || !$user->hasAnyRole($roles)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke resource ini.'], 403);
        }

        return $next($request);
    }
}
