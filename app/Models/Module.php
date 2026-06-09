<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'title',
    ];

    /**
     * Relationship: The subject this module belongs to.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relationship: Lectures in this module.
     */
    public function lectures()
    {
        return $this->hasMany(Lecture::class);
    }

    /**
     * Relationship: Study materials in this module.
     */
    public function materials()
    {
        return $this->hasMany(Material::class);
    }
}
