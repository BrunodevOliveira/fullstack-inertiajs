<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Exibe os dados do perfil do usuário autenticado.
    */
    public function edit(Request $request): Response
    {
        // load => carregue os relacionamentos perfis e aluno que Usuario possui.
        $user = $request->user()->load(['perfis', 'aluno']);

        return Inertia::render('profile/Edit', [
            'user' => [
                'id' => $user->id,
                'nome' => $user->nome,
                'nome_social' => $user->nome_social,
                'cpf' => $user->cpf,
                'email' => $user->email,
                'telefone' => $user->telefone,
                'lattes' => $user->lattes,
                'siape' => $user->siape,
                'sira' => $user->sira,
                'perfis' => $user->perfis->map(fn ($p) => [
                    'id' => $p->id,
                    'nome' => $p->nome,
                ]),
                'aluno' => $user->aluno ? [
                    'curso_importado' => $user->aluno->curso_importado,
                ] : null,
            ],
        ]);
    }

    /**
     * Atualiza as informações pessoais do usuário.
     * O laravel utiliza os métodos da classe UpdateProfileRequest para validar os dados antes de executar o update
    */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->update($request->validated());
        return redirect()
            ->route('profile.edit')
            ->with('success', 'Perfil atualizado com sucesso!');
    }
}
