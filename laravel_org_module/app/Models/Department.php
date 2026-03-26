<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id', 'institution_id', 'department_name', 'department_code',
        'hod_name', 'hod_contact', 'description', 'status'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
