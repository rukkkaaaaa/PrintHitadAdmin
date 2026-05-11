<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;
    protected $fillable = ['admin_id', 'task'];


    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
