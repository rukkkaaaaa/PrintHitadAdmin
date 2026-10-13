<?php

namespace App\Http\Controllers;

use App\Support\TeamAreaPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeamAccessController extends Controller
{
    private function authorizeAccessManagement(): void
    {
        $role = strtolower(trim((string) data_get(session('user'), 'role', '')));
        abort_unless(in_array($role, ['super admin', 'administrative level'], true), 403);
    }

    public function index()
    {
        $this->authorizeAccessManagement();

        $admins = DB::table('admins')
            ->select('id', 'admin_name', 'email', 'role')
            ->whereIn('role', array_values(TeamAreaPermissions::TEAMS))
            ->orderBy('role')
            ->orderBy('admin_name')
            ->get();
        $permissions = DB::table('admin_area_permissions')
            ->whereIn('admin_id', $admins->pluck('id'))
            ->get(['admin_id', 'area_key'])
            ->groupBy('admin_id')
            ->map(fn ($rows) => $rows->pluck('area_key')->all())
            ->all();
        $areas = TeamAreaPermissions::AREAS;

        return view('team-access.index', compact('permissions', 'admins', 'areas'));
    }

    public function update(Request $request)
    {
        $this->authorizeAccessManagement();

        $areaKeys = array_keys(TeamAreaPermissions::AREAS);
        $validated = $request->validate([
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => ['string', Rule::in($areaKeys)],
        ]);

        $teamAdminIds = DB::table('admins')
            ->whereIn('role', array_values(TeamAreaPermissions::TEAMS))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $submittedIds = array_map('intval', array_keys($validated['permissions'] ?? []));
        abort_unless(count(array_diff($submittedIds, $teamAdminIds)) === 0, 422);

        DB::transaction(function () use ($validated, $areaKeys, $teamAdminIds) {
            foreach ($teamAdminIds as $adminId) {
                $selected = array_values(array_unique($validated['permissions'][$adminId] ?? []));
                DB::table('admin_area_permissions')->where('admin_id', $adminId)->delete();

                foreach (array_intersect($selected, $areaKeys) as $areaKey) {
                    DB::table('admin_area_permissions')->insert([
                        'admin_id' => $adminId,
                        'area_key' => $areaKey,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        return redirect('/team-access')->with('success', 'Team access updated successfully.');
    }
}
