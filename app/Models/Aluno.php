<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aluno extends Model
{
    protected $table = 'alunos';

    protected $fillable = ['usuario_id', 'curso_importado'];

    /**
     * Usuário ao qual este registro de aluno pertence.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
