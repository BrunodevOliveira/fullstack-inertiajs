<?php

namespace App\Http\Controllers;

use App\Enums\PerfilEnum;
use App\Http\Requests\CampusRequest;
use App\Models\Campus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CampusController extends Controller
{
    /**
     * Garante que apenas Super Administrador (Root) ou Administrador acessem o recurso.
    */
    private function authorizedAdmin(Request $request): void {
        $user = $request->user();

        abort_unless(
            $user && ($user->hasPerfil(PerfilEnum::Root) || $user->hasPerfil(PerfilEnum::Administrador)),
            403,
            'Acesso restrito a administradores.'
        );
    }

    /**
     * Listagem paginada de campi com contagem de cursos e filtro de busca.
     */
    public function index(Request $request): Response
    {
        $this->authorizedAdmin($request);

        $query = Campus::withCount('cursos')->latest('id');

        if($busca = $request->input('busca')) {
            $query->where('nome', 'like', "%{$busca}%");
        }

        $campuses = $query->paginate(10)->withQueryString();

        return Inertia::render('admin/campuses/Index', [
            'campuses'=> $campuses,
            'filters' => [
                'busca'=> $busca ?? '', 
            ],
        ]);
    }

    /**
     * Cadastro de um novo campus.
    */
    public function store(CampusRequest $request): RedirectResponse 
    {
        $this->authorizedAdmin($request);
         Campus::create($request->validated());

        return redirect()->route('campuses.index')
            ->with('success', 'Campus atualizado com sucesso!');
    }

    /**
     * Atualização dos dados do campus.
     * Utiliza Route Model Binding-> $campus é convertido automaticamente em um objeto Campus
     *  Isso permite que o laravel faça uma consulta automatica no BD buscando os dados do campus, sem escrever Campus::findOrFail($id) 
     *  Para que essa consulta ocorra, o nome do parâmetro "$campus" deve ser igual ao declarado no web.php
    */
    public function update(CampusRequest $request, Campus $campus): RedirectResponse
    {
        $this->authorizedAdmin($request);

        //Pegue este Campus específico e atualize seus dados.
        $campus->update($request->validated());//validated->retorna os dados que passaram pela validação do CampusRequest

        return redirect()->route('campuses.index')
            ->with('success', 'Campus atualizado com sucesso!');

    }

    /**
     * Exclusão do campus com trava de segurança de integridade.
    */

    public function destroy(Request $request, Campus $campus): RedirectResponse
    {
        $this->authorizedAdmin($request);

        // Bloqueio de exclusão quando existirem cursos associados ao campus
        if($campus->cursos()->exists()) {
            return redirect()->route('campuses.index')
                ->with('error', 'Não é possível excluir este campus pois existem cursos vinculados a ele.');
        }

        $campus->delete();

        return redirect()->route('campuses.index')
            ->with('success', 'Campus excluído com sucesso!');
    }
}
