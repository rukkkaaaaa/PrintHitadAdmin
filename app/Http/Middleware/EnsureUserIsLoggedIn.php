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

        /*
        |--------------------------------------------------------------------------
        | Administrative Level
        |--------------------------------------------------------------------------
        | Full access to everything.
        */
        if ($role === 'administrative level') {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        | Full route access.    
        | Audit columns will be hidden in Blade separately.
        */
        if ($role === 'super admin') {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Team Chandana
        |--------------------------------------------------------------------------
        | ONLY:
        | - Lahipita Approved Ads
        | - Lahipita Print on Hitad Paper Ads
        */
        if ($role === 'team chandana') {

            $isAllowedPath =
                $request->is('advertisements/lahipita/approved') ||
                $request->is('advertisements/lahipita/approved/*/view') ||
                $request->is('advertisements/lahipita/print-on-paper') ||
                $request->is('advertisements/lahipita/print-on-paper/*/view') ||
                $request->is('logout');

            if (!$isAllowedPath) {
                return redirect('/advertisements/lahipita/approved')
                    ->with('error','Team Chandana can only access Lahipita approved and Print on Hitad Paper advertisements.');
            }

            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Team Nalaka
        |--------------------------------------------------------------------------
        | ONLY:
        | - Hitad Approved Ads
        */
        if ($role === 'team nalaka') {

            $isAllowedPath =
                $request->is('advertisements/hitad/approved') ||
                $request->is('advertisements/hitad/approved/*/view') ||
                $request->is('logout');

            if (!$isAllowedPath) {
                return redirect('/advertisements/hitad/approved')
                    ->with('error','Team Nalaka can only access Hitad approved advertisements.');
            }
            return $next($request);
        }
        $isReportingRole = in_array($role, ['reporting', 'reportingrole', 'report admin', 'reporter'], true);
        $isAdvertisingRole = in_array($role, ['advertice admin', 'advertising', 'advertising role', 'advertising admin'], true);

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

        if ($isAdvertisingRole) {
            $isAllowedPath =
                $request->getRequestUri() === '/' ||
                $request->is('dashboard') ||
                $request->is('advertisements/create') ||
                $request->is('advertisements/store') ||
                $request->is('all-print-ads') ||
                $request->is('advertisements') ||
                $request->is('advertisements/paid') ||
                $request->is('advertisements/unpaid') ||
                $request->is('advertisements/lahipita') ||
                $request->is('advertisements/lahipita/paid') ||
                $request->is('advertisements/lahipita/unpaid') ||
                $request->is('advertisements/*/view') ||
                $request->is('advertisements/*/edit') ||
                $request->is('advertisements/*/update') ||
                $request->is('advertisements/*/download') ||
                $request->is('advertisements/lahipita/*/view') ||
                $request->is('advertisements/lahipita/*/edit') ||
                $request->is('advertisements/lahipita/*/update') ||
                $request->is('advertisements/lahipita/*/download') ||
                $request->is('logout');

            if (!$isAllowedPath) {
                return redirect('/dashboard')->with('error', 'Advertising role can only access dashboard and advertisement pages.');
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
                'adtypes/by-category/*',
                'adsizes/by-type/*',
                'tints', 'add-tint', 'update-tint/*',
                'adcriterias', 'add-adcriteria', 'update-adcriteria/*',
                'adcriterias/by-category/*',
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
            'adtypes/by-category/*',
            'adsizes/by-type/*',
            'tints', 'add-tint', 'update-tint/*',
            'adcriterias', 'add-adcriteria', 'update-adcriteria/*',
            'adcriterias/by-category/*',
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
