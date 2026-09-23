<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Curso extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'cursos';
    protected $fillable = [
        'nome',
        'campus_id',
        'disciplina_id',
        'documento_id',
        'email',
        'colaborador',
    ];

    protected function casts(): array
    {
        return [
            'colaborador' => 'boolean'
        ];
    }

    /**
     * Campus ao qual o curso pertence.
     */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }

    /**
     * Disciplina de referência (limite de períodos PINC).
     */
    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class, 'disciplina_id');
    }

    /**
     * Departamentos / Laboratórios vinculados ao curso.
     */
    public function departamentos(): HasMany
    {
        return $this->hasMany(Departamento::class, 'curso_id');
    }
}
