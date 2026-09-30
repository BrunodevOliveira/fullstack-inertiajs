<?php

namespace App\Http\Controllers;

use App\Enums\PerfilEnum;
use App\Http\Requests\DepartamentoRequest;
use App\Models\Campus;
use App\Models\Curso;
use App\Models\Departamento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class DepartamentoController extends Controller
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
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorizedAdmin($request);
        /**
         * With->carrega relacionamentos
         * Where-> Quero filtrar a tabela que estou consultando
         * whereHas->Quero filtrar a tabela principal com base em uma condição existente em um relacionamento
         */
        $query = Departamento::with([
            'curso:id,nome,campus_id', // O primeiro curso carrega a relação direta.
            'curso.campus:id,nome', // carregar também o campus daquele curso.
        ])->latest('id');

        // Filtro por nome do departamento
        if ($busca = $request->input('busca')) {
            $query->where('nome', 'like', "%{$busca}%");
        }

        // Filtro por curso
        if ($cursoId = $request->input('curso_id')) {
            $query->where('curso_id', $cursoId);
        }

        // Filtro por campus (através da relação curso)
        if ($campusId = $request->input('campus_id')) {
            $query->whereHas('curso', function ($q) use ($campusId) {
                $q->where('campus_id', $campusId); // Aqui o where passa a se referir a tabela curso e não mais a departamentos
            });
        }

        $departamentos = $query->paginate(10)->withQueryString();

        $campuses = Campus::orderBy('nome')->get(['id', 'nome']);
        $cursos = Curso::with('campus:id,nome')->orderBy('nome')->get(['id', 'nome', 'campus_id']);

        return Inertia::render('admin/departamentos/Index', [
            'departamentos' => $departamentos,
            'campuses' => $campuses,
            'cursos' => $cursos,
            'filters' => [
                'busca' => $busca,
                'curso_id' => $cursoId ? (int) $cursoId : null,
                'campus_id' => $campusId ? (int) $campusId : null,
            ],
        ]);
    }

    /**
     * Cadastra um novo departamento/laboratório.
     */
    public function store(DepartamentoRequest $request): RedirectResponse
    {
        $this->authorizedAdmin($request);

        Departamento::create($request->validated());

        return redirect()->route('departamentos.index')
            ->with('success', 'Departamento/Laboratório cadastrado com sucesso!');

    }

    /**
     * Atualiza um departamento/laboratório existente.
     */
    public function update(DepartamentoRequest $request, Departamento $departamento): RedirectResponse
    {
        $this->authorizedAdmin($request);

        $departamento->update($request->validated());

        return redirect()->route('departamentos.index')
            ->with('success', 'Departamento/Laboratório atualizado com sucesso!');
    }

    /**
     * Exclui (soft delete) um departamento/laboratório garantindo integridade.
     */
    public function destroy(Request $request, Departamento $departamento)
    {
        $this->authorizedAdmin($request);

        // Prevenção de integridade referencial: impede exclusão se houver projetos vinculados (preparação para Épico 4)
        if (Schema::hasTable('projetos') && // Verifica se a tabela Projetos existe no BD
             method_exists($departamento, 'projetos') && // O objeto $departamento possui um método chamado projetos
             $departamento->projetos()->exists() // Existe pelo menos um projeto relacionado a este departamento?
        ) {
            return redirect()->route('departamentos.index')
                ->with('error', 'Não é possível excluir este departamento/laboratório pois existem projetos vinculados a ele.');
        }

        $departamento->delete();

        return redirect()->route('departamentos.index')
            ->with('success', 'Departamento/Laboratório excluído com sucesso!');
    }
}
