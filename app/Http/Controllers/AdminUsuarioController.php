<?php

namespace App\Http\Controllers;

use App\Enums\PerfilEnum;
use App\Models\Perfil;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminUsuarioController extends Controller
{
    public function showUsers(Request $request): Response
    {
        $user = $request->user();

        abort_unless(
            $user->hasPerfil(PerfilEnum::Root) ||  $user->hasPerfil(PerfilEnum::Administrador),
            403,
            'Acesso restrito a administradores'
        );
        
        // Método construtor with -> Inicia a montagem o SQL
        $query = Usuario::with('perfis')->latest('id');

        // Filtro por busca textual (Nome, CPF ou E-mail)
        if ($busca = $request->input('busca')) {
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                    ->orWhere('nome_social', 'like', "%{$busca}%")
                    ->orWhere('cpf', 'like', "%{$busca}%")
                    ->orWhere('email', 'like', "%{$busca}%");
            });
        }

        // Filtro por perfil
        if ($perfilId = $request->input('perfil_id')) {
            $query->whereHas('perfis', function ($q) use ($perfilId) { //o whereHas gera uma subconsulta com WHERE EXISTS
                $q->where('perfis.id', $perfilId); //O $q aqui representa a tabela de perfis.
            });
        }

        // Filtro por Situação (Ativo / Inativo)
        if($request->has('situacao')){
            $query->where('situacao',  $request->boolean('situacao'));
        }

        // Método Executor paginate-> Aqui finalizamos a montagem da query e enviamos ao DB
        $usuarios = $query->paginate(10)->withQueryString();

        $perfis = Perfil::orderBy('id')->get([ 'id', 'nome']);

        return Inertia::render('admin/usuarios/Index', [
            'usuarios'=> $usuarios,
            'perfis' => $perfis,
            'filters' => [
                'busca' => $request->input('busca', ''),
                'perfil_id' => $request->input('perfil_id', ''),
                'situacao' => $request->input('situacao', ''),
            ],
            'can_impersonate' => $user->hasPerfil(PerfilEnum::Root),
        ]);
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        $currentUser = $request->user();
        
        abort_unless(
            $currentUser->hasPerfil(PerfilEnum::Root) || $currentUser->hasPerfil(PerfilEnum::Administrador),
            403,
            'Acesso restrito a administradores.'
        );

        $dados = $request->validate([
            'perfis' => ['required', 'array', 'min:1'],
            'perfis.*' => ['integer', 'exists:perfis,id'],
            'situacao' => ['required', 'boolean'],
        ], [
            'perfis.required' => 'Selecione pelo menos um perfil para o usuário.',
            'perfis.min' => 'O usuário deve possuir pelo menos um perfil.',
        ]);

        // Trava de segurança: Root não pode desativar a si mesmo nem remover o próprio perfil Root
        if ($currentUser->id === $usuario->id) {
            
            if (! in_array(PerfilEnum::Root->value, $dados['perfis']) && $currentUser->hasPerfil(PerfilEnum::Root)) {
                return back()->with('error', 'Você não pode remover o seu próprio perfil de Super Administrador.');
            }

            if (! $dados['situacao']) {
                return back()->with('error', 'Você não pode desativar a sua própria conta.');
            }
        }

        // Sincroniza os perfis na tabela pivô
        $usuario->perfis()->sync($dados['perfis']);

        // Atualiza a situação (ativo/inativo)
        $usuario->update([
            'situacao' => $dados['situacao'],
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', "Usuário {$usuario->nome} atualizado com sucesso!");
    }
}
