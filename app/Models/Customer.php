<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = ['customer_name', 'address', 'telephone', 'nic_passport', 'email', 'email_verified', 'is_active'];


    public function advertisements()
    {
        return $this->hasMany(Advertisement::class);
    }
}
