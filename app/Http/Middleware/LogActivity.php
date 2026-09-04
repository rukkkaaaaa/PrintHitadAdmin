<?php

namespace App\Http\Middleware;

use App\Models\Log;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Automatically records every state-changing (non-GET) admin request into
 * the logs / admin_has_logs tables, so no controller needs to log manually.
 */
class LogActivity
{
    /**
     * Routes that are excluded from automatic activity logging (noisy helper/AJAX endpoints).
     */
    private const EXCLUDED_PATHS = [
        'calculate-ad-price',
        'login',
        'logout',
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $this->recordActivity($request, $response);

        return $response;
    }

    private function recordActivity(Request $request, $response): void
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        if (in_array(trim($request->path(), '/'), self::EXCLUDED_PATHS, true)) {
            return;
        }

        if (method_exists($response, 'getStatusCode') && $response->getStatusCode() >= 400) {
            return;
        }

        $adminId = data_get(Session::get('user'), 'id');
        if (!$adminId) {
            return;
        }

        Log::record((int) $adminId, $this->buildTaskDescription($request));
    }

    private function buildTaskDescription(Request $request): string
    {
        $route = $request->route();
        $action = $route ? $route->getActionMethod() : null;

        $label = $action && $action !== 'Closure'
            ? Str::ucfirst(Str::snake($action, ' '))
            : ($request->method() . ' ' . $request->path());

        $params = $route ? array_filter($route->parameters(), 'is_scalar') : [];

        if (!empty($params)) {
            $label .= ' (' . implode(', ', $params) . ')';
        }

        return $label;
    }
}
