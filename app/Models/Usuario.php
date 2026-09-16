<?php

namespace App\Models;

use App\Enums\PerfilEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';

    protected $fillable = [
        'cpf',
        'nome',
        'nome_social',
        'email',
        'telefone',
        'lattes',
        'siape',
        'sira',
        'situacao',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // O casts() é o "tradutor bidirecional" do Laravel:
    // ele converte os dados ao sair do banco para o PHP e ao sair do PHP para o banco.
    protected function casts(): array
    {
        return [
            'situacao' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Perfis atribuídos ao usuário.
     */
    public function perfis(): BelongsToMany
    {
        return $this->belongsToMany(
            Perfil::class,  // 1. Related Model (Com quem estou me relacionando?)
            'perfil_usuario', // 2. Table (Qual é a tabela pivô no banco?)
            'usuario_id', // 3. ForeignPivotKey (Qual coluna na pivô aponta para MIM?)
            'perfil_id' // 4. RelatedPivotKey (Qual coluna na pivô aponta para o OUTRO?)
        );
    }

    /**
     * Verifica se o usuário possui determinado perfil.
     */
    public function hasPerfil(PerfilEnum $perfil): bool
    {
        return $this->perfis->contains('id', $perfil->value);
    }
}
