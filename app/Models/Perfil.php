<?php

namespace App\Models;

use App\Enums\PerfilEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Perfil extends Model
{
    use HasFactory;

    // Indicamos ao Laravel que a tabela possui esse nome em português(o padrão é ingles)
    protected $table = 'perfis';

    // Se um usuário mal-intencionado enviar um campo falso pelo formulário
    // (ex: is_admin = true), o Laravel vai ignorar esse campo porque ele não está no $fillable
    protected $fillable = [
        'id',
        'nome',
        'descricao',
    ];

    /**
     * Retorna a representação tipada do Enum correspondente a este perfil.
     * Converte o Model em uma instância do PerfilEnum (PerfilEnum::Docente)
     */
    public function toEnum(): ?PerfilEnum
    {
        return PerfilEnum::tryFrom($this->id);
    }

    /**
     * Usuários que possuem este perfil.
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            Usuario::class,
            'perfil_usuario',
            'perfil_id', // Quem sou EU na tabela pivô
            'usuario_id' // Quem é o OUTRO na tabela pivô
        );
    }
}
