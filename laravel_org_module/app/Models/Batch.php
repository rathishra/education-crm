<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id', 'institution_id', 'department_id', 'course_id',
        'batch_name', 'academic_year', 'start_date', 'end_date',
        'intake_capacity', 'current_strength', 'status'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}
