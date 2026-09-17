<?php

namespace App\Http\Controllers;

use App\Enums\PerfilEnum;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    /**
     * Inicia a personificação de um usuário alvo.
    */
    public function start(Request $request, Usuario $usuario): RedirectResponse 
    {
        $currentUser = $request->user();

        // Apenas usuários com perfil Root podem iniciar a personificação
        abort_unless(
            $currentUser->hasPerfil(PerfilEnum::Root),
            403,
            'Apenas o Super Administrador (Root) pode personificar outros usuários.'
        );
        
        //Não permite personificar a si mesmo
        if($currentUser->id === $usuario->id){
            return back()->with('error', 'Você já está conectado na sua própria conta.');
        }

        // Não permite personificar usuários inativos
        if (! $usuario->situacao) {
            return back()->with('error', 'Não é possível personificar um usuário inativo.');
        }

        // Guarda o ID do Root original na sessão
        $request->session()->put('impersonator_id',  $currentUser->id);

        // Faz o login como o novo usuário
        Auth::login($usuario);

        return redirect()
            ->route('home')
            ->with('warn', "Você agora está navegando como {$usuario->nome}.");
    }

    /**
     * Encerra a personificação e retorna para a conta Root original.
    */
    public function leave(Request $request): RedirectResponse 
    {
        abort_unless(
            $request->session()->has('impersonator_id'),
            403,
            'Nenhuma sessão de personificação ativa.'
        );

        $impersonatorId = $request->session()->pull('impersonator_id');
        $originalUser = Usuario::findOrFail($impersonatorId);

        Auth::login($originalUser);

        return redirect()
            ->route('home')
            ->with('info',
             "Personificação encerrada. Bem-vindo de volta, {$originalUser->nome}."
            );
    }
}
