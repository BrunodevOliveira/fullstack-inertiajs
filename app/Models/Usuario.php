<?php

namespace App\Models;

use App\Enums\PerfilEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    use HasFactory, SoftDeletes;

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

    /**
     * Dados acadêmicos de aluno (caso o usuário seja discente).
     */
    public function aluno(): HasOne
    {
        return $this->hasOne(Aluno::class, 'usuario_id');
    }

    /**
     * Logs de auditoria de acessos do usuário.
     */
    public function logUsers(): HasMany
    {
        return $this->hasMany(LogUser::class, 'usuario_id');
    }

    public function projetosComoResponsavel(): HasMany
    {
        return $this->hasMany(Projeto::class, 'responsavel_id');
    }

    public function projetosParticipados(): BelongsToMany
    {
        return $this->belongsToMany(
            Projeto::class,
            'projeto_usuario',
            'usuario_id',
            'projeto_id'
        )
            ->withPivot('flags')// Adicionamos isso para que a model tenha acesso também a coluna flags da tabela pivot (por padrão ela só tem acesso as colunas de id que vinculam as tabelas relacionadas ao pivot)
            ->withTimestamps();
    }
}
