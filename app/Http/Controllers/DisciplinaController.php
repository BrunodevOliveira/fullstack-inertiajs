<?php

namespace App\Http\Controllers;

use App\Enums\PerfilEnum;
use App\Http\Requests\DisciplinaRequest;
use App\Models\Disciplina;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class DisciplinaController extends Controller
{
    /**
     * Garante que apenas Super Administrador (Root) ou Administrador acessem o recurso.
     */
    private function authorizedAdmin(Request $request): void
    {
        $user = $request->user();
        abort_unless(
            $user && ($user->hasPerfil(PerfilEnum::Root) || $user->hasPerfil(PerfilEnum::Administrador)),
            403,
            'Acesso restrito a administradores.'
        );
    }

    public function index(Request $request): Response
    {
        $this->authorizedAdmin($request);
        
        // orderByRaw()-> permite colocar uma expressão SQL diretamente no ORDER BY, em vez de ordenar simplesmente por uma coluna.
        $query = Disciplina::withCount('cursos')
            ->orderByRaw('LENGTH(nome) ASC, nome ASC');//Ordena pelo tamanho do nome, havendo empate, pelo nome alfabeticamente

        if ($busca = $request->input('busca')) {
            $query->where('nome', 'like', "%{$busca}%");
        }

        $disciplinas = $query->paginate(10)->withQueryString();

        return Inertia::render('admin/disciplinas/Index', [
            'disciplinas' => $disciplinas,
            'filters' => [
                'busca' => $busca ?? '',
            ]
        ]);
    }

    /**
     * Cadastro de uma nova disciplina / período do programa.
     */
    public function store(DisciplinaRequest $request): RedirectResponse
    {
        $this->authorizedAdmin($request);

        Disciplina::create($request->validated());

        return redirect()->route('disciplinas.index')
            ->with('success', 'Disciplina/Período cadastrado com sucesso!');
    }

    /**
     * Atualização dos dados da disciplina / período.
     */
    public function update(DisciplinaRequest $request, Disciplina $disciplina): RedirectResponse
    {
        $this->authorizedAdmin($request);

        $disciplina->update($request->validated());

        return redirect()->route('disciplinas.index')
            ->with('success', 'Disciplina/Período atualizado com sucesso!');
    }

    /**
     * Exclusão da disciplina com travas de segurança de integridade referencial.
     */
    public function destroy(Request $request, Disciplina $disciplina): RedirectResponse
    {
        $this->authorizedAdmin($request);

        // Bloqueio de integridade: impede exclusão se houver avaliações vinculadas (requisito PINC / Épico 6)
        if (
            Schema::hasTable('avaliacoes') &&
            method_exists($disciplina, 'avaliacoes') &&
            $disciplina->avaliacoes()->exists()
        ) {
            return redirect()->route('disciplinas.index')
                ->with('error', 'Não é possível excluir esta disciplina/período pois existem avaliações vinculadas a ela.');
        }

        // Bloqueio de integridade: impede exclusão se houver cursos vinculados como referência
        if ($disciplina->cursos()->exists()) {
            return redirect()->route('disciplinas.index')
                ->with('error', 'Não é possível excluir esta disciplina/período pois existem cursos vinculados a ela.');
        }

        $disciplina->delete();

        return redirect()->route('disciplinas.index')
            ->with('success', 'Disciplina/Período excluído com sucesso!');
    }
}
