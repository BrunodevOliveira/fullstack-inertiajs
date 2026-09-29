<?php

namespace App\Http\Controllers;

use App\Enums\PerfilEnum;
use App\Http\Requests\CursoRequest;
use App\Models\Campus;
use App\Models\Curso;
use App\Models\Disciplina;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CursoController extends Controller
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

    /**
     * Listagem paginada de cursos com filtros por nome e campus, e contagem de departamentos.
     */
    public function index(Request $request): Response
    {
        $this->authorizedAdmin($request);

        /**
         * with() é utilizado para fazer Eager Loading -> Já carregue os relacionamentos junto com os registros principais.
         * Busca os cursos e, junto com eles, carregue também o campus e a disciplina relacionados, trazendo apenas id e nome dessas relações
         */
        $query = Curso::with(['campus:id,nome', 'disciplina:id,nome'])
            ->withCount('departamentos')// quantidade de departamentos associados a cada curso->departamentos_count
            ->latest('id');

        // Filtro por nome do curso
        if ($busca = $request->input('busca')) {
            $query->where('nome', 'like', "%{$busca}%");
        }

        // Filtro por campus
        if ($campusId = $request->input('campus_id')) {
            $query->where('campus_id', $campusId);
        }

        $cursos = $query->paginate(10)->withQueryString();

        // Dados auxiliares para filtros e formulários
        $campuses = Campus::orderBy('nome')->get(['id', 'nome']);
        $disciplinas = Disciplina::orderBy('id')->get(['id', 'nome']);

        return Inertia::render('admin/cursos/Index', [
            'cursos' => $cursos,
            'campuses' => $campuses,
            'disciplinas' => $disciplinas,
            'filters' => [
                'busca' => $busca,
                'campus_id' => $campusId ? (int) $campusId : null,
            ],
        ]);
    }

    public function store(CursoRequest $request): RedirectResponse
    {
        $this->authorizedAdmin($request);

        Curso::create($request->validated());

        return redirect()->route('cursos.index')
            ->with('success', 'Curso cadastrado com sucesso!');
    }

    public function update(CursoRequest $request, Curso $curso): RedirectResponse
    {
        $this->authorizedAdmin($request);

        $curso->update($request->validated());

        return redirect()->route('cursos.index')
            ->with('success', 'Curso atualizado com sucesso!');
    }

    public function destroy(Request $request, Curso $curso): RedirectResponse
    {
        $this->authorizedAdmin($request);

        if ($curso->departamentos()->exists()) {
            return redirect()->route('cursos.index')
                ->with('error', 'Não é possível excluir este curso pois existem departamentos/laboratórios vinculados a ele.');
        }

        $curso->delete();

        return redirect()->route('cursos.index')
            ->with('success', 'Curso excluído com sucesso!');
    }
}
