<?php

namespace App\Http\Middleware;

use App\Support\TeamAreaActivityLog;
use App\Support\TeamAreaPermissions;
use Closure;
use Illuminate\Http\Request;

class LogTeamAreaActions
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $status = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 200;

        if ($status < 200 || $status >= 300 || !$request->isMethod('GET')) {
            return $response;
        }

        $areaKey = TeamAreaPermissions::areaForRequest($request);
        if ($areaKey === null) {
            return $response;
        }

        $advertisementId = $request->route('id');
        TeamAreaActivityLog::record(
            $areaKey,
            'View',
            is_scalar($advertisementId) ? (int) $advertisementId : null
        );

        return $response;
    }
}
