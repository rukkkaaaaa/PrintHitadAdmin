<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;
    protected $fillable = [
        'admin_name',
        'priority_level',
        'email',
        'password',
        'time_logged_in',
        'is_active',
        'img_url'
    ];


    public function logs()
    {
        return $this->hasMany(Log::class);
    }
}
