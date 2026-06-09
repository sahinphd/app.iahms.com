<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'file_path',
    ];

    /**
     * Relationship: The module this material belongs to.
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
