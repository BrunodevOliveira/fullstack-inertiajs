<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departamento extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'departamentos';

    protected $fillable = [
        'nome',
        'curso_id',
    ];

    /**
     * Curso ao qual o departamento/laboratório está subordinado.
     */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function projetos(): HasMany {
        return $this->hasMany(Projeto::class, 'departamento_id');
    }
}
