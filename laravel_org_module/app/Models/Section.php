<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id', 'institution_id', 'batch_id', 'section_name', 'capacity'
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
