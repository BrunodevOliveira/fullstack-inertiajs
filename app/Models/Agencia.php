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
}
