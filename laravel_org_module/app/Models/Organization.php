<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_name', 'organization_code', 'logo', 'address', 'city',
        'state', 'country', 'pincode', 'phone', 'email', 'website', 'timezone',
        'currency', 'subscription_plan', 'subscription_start', 'subscription_end',
        'max_institutions', 'status'
    ];

    public function institutions()
    {
        return $this->hasMany(Institution::class);
    }

    public function campuses()
    {
        return $this->hasMany(Campus::class);
    }
}
