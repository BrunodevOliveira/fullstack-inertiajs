<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'campuses';
    protected $fillable = [
        'nome',
    ];

    /**
     * Cursos oferecidos neste campus.
     */
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'campus_id');
    }
}
