<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id', 'institution_id', 'department_id', 'course_name',
        'course_code', 'course_type', 'degree_type', 'duration_years',
        'total_semesters', 'credits', 'intake_capacity', 'status'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }
}
