<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lecture extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'file_path',
        'duration',
    ];

    /**
     * Relationship: The module this lecture belongs to.
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
