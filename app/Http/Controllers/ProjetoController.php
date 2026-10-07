<?php

namespace App\Http\Controllers;

use App\Actions\CriarProjetoAction;
use App\Http\Requests\ProjetoRequest;
use App\Models\Agencia;
use App\Models\Curso;
use App\Models\Departamento;
use App\Models\Projeto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProjetoController extends Controller
{
    // Exibe o formulário de criação do projeto
    public function create(Request $request): Response
    {

        Gate::authorize('create', Projeto::class);

        $cursos = Curso::orderBy('nome')->get(['id', 'nome']);
        $departamentos = Departamento::orderBy('nome')->get(['id', 'nome', 'curso_id']);
        $agencias = Agencia::orderBy('nome')->get(['id', 'nome']);

        return Inertia::render('projetos/Create', [
            'cursos' => $cursos,
            'departamentos' => $departamentos,
            'agencias' => $agencias,
        ]);
    }

    // Cria o projeto no BD
    public function store(ProjetoRequest $request, CriarProjetoAction $create): RedirectResponse
    {
        Gate::authorize('create', Projeto::class);

        $dados = $request->validated();
        $create->execute($dados, $request->user());

        return redirect()->route('home')
            ->with('success', 'Projeto cadastrado com sucesso!');
    }

    // Exibe página de edição
    public function edit(Request $request, Projeto $projeto): Response
    {
        Gate::authorize('update', $projeto);
        $projeto->load('departamento.curso');

        $cursos = Curso::orderBy('nome')->get(['id', 'nome']);
        $departamentos = Departamento::orderBy('nome')->get(['id', 'nome', 'curso_id']);
        $agencias = Agencia::orderBy('nome')->get(['id', 'nome']);

        return Inertia::render('projetos/Edit', [
            'projeto' => $projeto,
            'cursos' => $cursos,
            'departamentos' => $departamentos,
            'agencias' => $agencias,
        ]);
    }

    public function update(ProjetoRequest $request, Projeto $projeto): RedirectResponse
    {
        Gate::authorize('update', $projeto);
        $projeto->update($request->validated());

        return redirect()->route('home')
            ->with('success', 'Projeto atualizado com sucesso!');
    }
}
