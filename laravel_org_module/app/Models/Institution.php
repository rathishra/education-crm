<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id', 'institution_name', 'institution_code', 'institution_type',
        'affiliation', 'university', 'establishment_year', 'address', 'contact_number',
        'email', 'website', 'principal_name', 'principal_contact', 'logo', 'status'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function campuses()
    {
        return $this->hasMany(Campus::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}
