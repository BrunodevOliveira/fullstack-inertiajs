<?php

namespace App\Policies;

use App\Enums\PerfilEnum;
use App\Models\Projeto;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class ProjetoPolicy
{
    // Executa sempre que ProjetoPolicy é chamado antes de qualquer outro mmétodo.
    // Se retornar true, pula a execução dos  métodos da policy, caso contrário executa o método especifico
    public function before(Usuario $usuario, string $ability): ?bool
    {
        if ($usuario->hasPerfil(PerfilEnum::Root) ||
            $usuario->hasPerfil(PerfilEnum::Administrador)
        ) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Usuario $usuario): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Usuario $usuario, Projeto $projeto): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Usuario $usuario): Response
    {

        if (! $usuario->hasPerfil(PerfilEnum::Docente)) {
            return Response::deny('usuário não possui perfil docente para prosseguir com a ação.');
        }

        if (blank($usuario->lattes)) {
            return Response::deny('usuário não possui lattes cadastrado.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Usuario $usuario, Projeto $projeto): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Usuario $usuario, Projeto $projeto): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Usuario $usuario, Projeto $projeto): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Usuario $usuario, Projeto $projeto): bool
    {
        return false;
    }
}
