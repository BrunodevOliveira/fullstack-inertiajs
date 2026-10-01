<?php

namespace App\Models;

use App\Enums\AgenciaTipoEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agencia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'agencias';

    protected $fillable = [
        'sigla',
        'nome',
        'tipo',
    ];

    /**
     * Atributos extras que devem ser anexados na serialização do modelo.
     * "Quando esse Model for convertido para array/JSON, inclua também um atributo chamado is_sem_bolsa, mesmo ele não existindo como coluna na tabela."
     */
    protected $appends = [
        'is_sem_bolsa',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => AgenciaTipoEnum::class,
        ];
    }

    /**
     * Identifica se a agência é o registro especial de "Sem Bolsa".
     */
    public function isSemBolsa(): bool
    {
        return mb_strtolower($this->sigla) === 'sem bolsa'
            || mb_strtolower($this->nome) === 'sem bolsa';
    }

    /**
     * Accessor para disponibilizar o status de "Sem Bolsa" para o frontend.
     * O Laravel pega o nome que você colocou no $appends, remove os underlines (_), coloca cada palavra com a primeira letra maiúscula (StudlyCase) e envolve com get...Attribute().
     */
    public function getIsSemBolsaAttribute(): bool
    {
        return $this->isSemBolsa();
    }
}
