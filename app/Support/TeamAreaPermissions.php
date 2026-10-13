<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeamAreaPermissions
{
    public const TEAMS = [
        'team_chandana' => 'team chandana',
        'team_nalaka' => 'team nalaka',
    ];

    public const AREAS = [
        'lahipita_web_combined_ads' => [
            'label' => 'Lahipita Web Combined Ads',
            'path' => 'advertisements/lahipita/web-combined-ads',
            'patterns' => [['GET', 'advertisements/lahipita/web-combined-ads']],
        ],
        'translate' => [
            'label' => 'Translate',
            'path' => 'advertisements/lahipita/translate',
            'active_pattern' => 'advertisements/lahipita/translate*',
            'patterns' => [
                ['GET', 'advertisements/lahipita/translate'],
                ['GET', 'advertisements/lahipita/translate/*'],
                ['POST', 'advertisements/lahipita/translate/*/complete'],
            ],
        ],
        'lahipita_approved_ads' => [
            'label' => 'Lahipita Approved Ads',
            'path' => 'advertisements/lahipita/approved',
            'patterns' => [
                ['GET', 'advertisements/lahipita/approved'],
                ['POST', 'advertisements/lahipita/approved/*/view'],
            ],
        ],
        'print_on_hitad_paper_ads' => [
            'label' => 'Print on Hitad Paper Ads',
            'path' => 'advertisements/lahipita/print-on-paper',
            'patterns' => [
                ['GET', 'advertisements/lahipita/print-on-paper'],
                ['POST', 'advertisements/lahipita/print-on-paper/*/view'],
            ],
        ],
        'hitad_approved_ads' => [
            'label' => 'Hitad Approved Ads',
            'path' => 'advertisements/hitad/approved',
            'patterns' => [
                ['GET', 'advertisements/hitad/approved'],
                ['POST', 'advertisements/hitad/approved/*/view'],
            ],
        ],
        'hitad_web_combined_ads' => [
            'label' => 'Hitad Web Combined Ads',
            'path' => 'web-combined-ads',
            'patterns' => [['GET', 'web-combined-ads']],
        ],
    ];

    public static function isTeamRole(string $role): bool
    {
        return in_array(strtolower(trim($role)), self::TEAMS, true);
    }

    public static function hasAccess($adminId, string $role, string $area): bool
    {
        $role = strtolower(trim($role));

        if (!$adminId || !self::isTeamRole($role) || !array_key_exists($area, self::AREAS)) {
            return false;
        }

        if (!Schema::hasTable('admin_area_permissions')) {
            return false;
        }

        return DB::table('admin_area_permissions')
            ->where('admin_id', $adminId)
            ->where('area_key', $area)
            ->exists();
    }

    public static function grantedAreas($adminId, string $role): array
    {
        $role = strtolower(trim($role));

        if (!$adminId || !self::isTeamRole($role) || !Schema::hasTable('admin_area_permissions')) {
            return [];
        }

        $grantedKeys = DB::table('admin_area_permissions')
            ->where('admin_id', $adminId)
            ->pluck('area_key')
            ->all();

        return array_intersect_key(self::AREAS, array_flip($grantedKeys));
    }

    public static function areaForRequest(Request $request): ?string
    {
        foreach (self::AREAS as $areaKey => $area) {
            foreach ($area['patterns'] as [$method, $pattern]) {
                if ($request->isMethod($method) && $request->is($pattern)) {
                    return $areaKey;
                }
            }
        }

        return null;
    }
}
