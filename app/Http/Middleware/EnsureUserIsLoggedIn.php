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

        // Site admin restriction: site admins can ONLY access specific admin pages
        $isSiteAdmin = in_array($role, ['site admin'], true);

        if ($isSiteAdmin) {
            // Allowed paths for site admin: categories, adtypes, adsizes, tints, adcriterias, adcriteria-options, districts, cities, dashboard, logout
            $siteAdminAllowedPatterns = [
                'categories', 'add-category', 'update-category/*',
                'adtypes', 'add-adtype', 'update-adtype/*',
                'adsizes', 'add-adsize', 'update-adsize/*',
                'tints', 'add-tint', 'update-tint/*',
                'adcriterias', 'add-adcriteria', 'update-adcriteria/*',
                'adcriteria-options', 'add-adcriteria-option', 'update-adcriteria-option/*',
                'districts', 'add-district', 'update-district/*',
                'cities', 'add-city', 'update-city/*',
                'dashboard',
                'logout'
            ];

            $isAllowedForSiteAdmin = false;
            foreach ($siteAdminAllowedPatterns as $pattern) {
                if ($request->is($pattern) || $request->getRequestUri() === '/') {
                    $isAllowedForSiteAdmin = true;
                    break;
                }
            }

            if (!$isAllowedForSiteAdmin) {
                return redirect('/dashboard')->with('error', 'Site admin can only access admin configuration pages.');
            }
        }

        // Prevent non-admins from accessing admin pages
        $adminOnlyPatterns = [
            'categories', 'add-category', 'update-category/*',
            'adtypes', 'add-adtype', 'update-adtype/*',
            'adsizes', 'add-adsize', 'update-adsize/*',
            'tints', 'add-tint', 'update-tint/*',
            'adcriterias', 'add-adcriteria', 'update-adcriteria/*',
            'adcriteria-options', 'add-adcriteria-option', 'update-adcriteria-option/*',
            'districts', 'add-district', 'update-district/*',
            'cities', 'add-city', 'update-city/*',
        ];

        foreach ($adminOnlyPatterns as $pattern) {
            if ($request->is($pattern)) {
                if (!$isSiteAdmin) {
                    // Non-admins attempting admin area are redirected to dashboard or reports
                    if ($isReportingRole) {
                        return redirect('/reports')->with('error', 'Access denied.');
                    }
                    return redirect('/dashboard')->with('error', 'Access denied.');
                }
                break;
            }
        }

        return $next($request);
    }
}
