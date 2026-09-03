<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Log extends Model
{
    use HasFactory;
    protected $fillable = ['admin_id', 'task'];


    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Record an activity for an admin and mirror it into the admin_has_logs pivot table.
     */
    public static function record(int $adminId, string $task): self
    {
        $log = static::create([
            'admin_id' => $adminId,
            'task' => $task,
        ]);

        DB::table('admin_has_logs')->insert([
            'admin_id' => $adminId,
            'log_id' => $log->id,
        ]);

        return $log;
    }
}
