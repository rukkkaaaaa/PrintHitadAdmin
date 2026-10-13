<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeamAreaActivityLog
{
    public static function record(string $areaKey, string $action, ?int $advertisementId = null): void
    {
        if (!array_key_exists($areaKey, TeamAreaPermissions::AREAS)
            || !Schema::hasTable('team_area_activity_logs')) {
            return;
        }

        $user = session('user', []);
        $adminId = data_get($user, 'id');
        if (!$adminId) {
            return;
        }

        DB::table('team_area_activity_logs')->insert([
            'admin_id' => $adminId,
            'admin_name' => (string) data_get($user, 'name', 'Unknown'),
            'admin_role' => (string) data_get($user, 'role', 'unknown'),
            'area_key' => $areaKey,
            'action' => $action,
            'advertisement_id' => $advertisementId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
