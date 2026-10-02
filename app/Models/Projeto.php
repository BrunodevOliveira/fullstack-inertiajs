<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Projeto extends Model
{
    use SoftDeletes, HasFactory;
    protected $table = 'projetos';
    protected $fillable = [
        'titulo',
        'assunto',
        'descricao',
        'vagas',
        'arquivado',
        'departamento_id', //Projeto pertence a um Departamento
        'responsavel_id', //Projeto pertence a um Responsável
        'agencia_id' //Projeto pertence a uma Agencia
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'vagas' => 'integer',
            'arquivado' => 'boolean',
        ];
    }

    //⚠️ Quem guarda a chave estrangeira (..._id) na sua própria tabela SEMPRE usa belongsTo!!!
    public function responsavel(): BelongsTo
    {   
        // withTrashed -> Permite resgatar o usuario responsável pelo projeto mesmo que ele tenha sido SoftDEletado
        return $this->belongsTo(Usuario::class, 'responsavel_id')->withTrashed();
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'departamento_id')->withTrashed();
    }

    public function agencia(): BelongsTo
    {
        return $this->belongsTo(Agencia::class, 'agencia_id')->withTrashed();
    }

    /**
     * Escopo para filtrar apenas projetos que estão ativos (não arquivados).
     */
    public function scopeAtivos(Builder $query): void
    {
        $query->where(function (Builder $q) {
            $q->where('arquivado', false)->orWhereNull('arquivado');
        });
    }

    /**
     * Escopo para filtrar apenas projetos que foram arquivados.
     */
    public function scopeArquivados(Builder $query): void
    {
        $query->where('arquivado', true);
    }
}
