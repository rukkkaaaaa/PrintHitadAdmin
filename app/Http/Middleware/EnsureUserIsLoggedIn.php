<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EnsureUserIsLoggedIn
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('user')) {
            return redirect('/login')->with('error', 'You must login first.');
        }

        $user = Session::get('user', []);
        $role = strtolower(trim((string) ($user['role'] ?? '')));
        $isReportingRole = in_array($role, ['reporting', 'reportingrole'], true);

        if ($isReportingRole) {
            $isAllowedPath = $request->is('reports')
                || $request->is('reports/*')
                || $request->is('logout')
                || $request->is('dashboard')
                || $request->getRequestUri() === '/';

            if (!$isAllowedPath) {
                return redirect('/reports')->with('error', 'Reporting role can only access reports and dashboard.');
            }
        }

        return $next($request);
    }
}
