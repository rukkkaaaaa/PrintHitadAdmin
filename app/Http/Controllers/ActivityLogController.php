<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function index()
    {
        $role = strtolower(trim((string) data_get(session('user'), 'role', '')));
        abort_unless(in_array($role, ['super admin', 'administrative level'], true), 403);

        $activities = DB::table('logs')
            ->leftJoin('admins', 'logs.admin_id', '=', 'admins.id')
            ->select('logs.id', 'logs.task', 'logs.created_at', 'admins.admin_name')
            ->orderByDesc('logs.created_at')
            ->orderByDesc('logs.id')
            ->paginate(25);

        return view('activity-logs.index', compact('activities'));
    }
}
