<?php

namespace App\Http\Controllers;

use App\Enums\AgenciaTipoEnum;
use App\Enums\PerfilEnum;
use App\Http\Requests\AgenciaRequest;
use App\Models\Agencia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class AgenciaController extends Controller
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
     * Listagem paginada com filtros por busca textual e tipo de agência.
     */
    public function index(Request $request): Response
    {
        $this->authorizedAdmin($request);

        $query = Agencia::query()->orderBy('sigla', 'asc');

        if ($busca = $request->input('busca')) {
            $query->where(function ($q) use ($busca) {
                $q->where('sigla', 'like', "%{$busca}%")
                    ->orWhere('nome', 'like', "%{$busca}%");
            });
        }

        if ($tipo = $request->input('tipo')) {
            $query->where('tipo', $tipo);
        }

        $agencias = $query->paginate(10)->withQueryString();

        return Inertia::render('admin/agencias/Index', [
            'agencias' => $agencias,
            'tipos' => AgenciaTipoEnum::options(),
            'filters' => [
                'busca' => $busca ?? '',
                'tipo' => $tipo ? (int) $tipo : null,
            ],
        ]);
    }

    /**
     * Cadastro de uma nova agência de fomento.
     */
    public function store(AgenciaRequest $request): RedirectResponse
    {
        $this->authorizedAdmin($request);

        Agencia::create($request->validated());

        return redirect()->route('agencias.index')
            ->with('success', 'Agência de fomento cadastrada com sucesso!');
    }

    /**
     * Atualização dos dados da agência.
     */
    public function update(AgenciaRequest $request, Agencia $agencia): RedirectResponse
    {
        $this->authorizedAdmin($request);

        $data = $request->validated();

        // Preserva a sigla original do registro fixo "Sem Bolsa"
        if ($agencia->isSemBolsa()) {
            $data['sigla'] = $agencia->sigla;
        }

        $agencia->update($data);

        return redirect()->route('agencias.index')
            ->with('success', 'Agência de fomento atualizada com sucesso!');
    }

    /**
     * Exclusão da agência com travas de integridade referencial e proteção do "Sem Bolsa".
     */
    public function destroy(Request $request, Agencia $agencia): RedirectResponse
    {
        $this->authorizedAdmin($request);

        // Bloqueio de segurança: o registro especial "Sem Bolsa" não pode ser excluído
        if ($agencia->isSemBolsa()) {
            return redirect()->route('agencias.index')
                ->with('error', 'A agência "Sem Bolsa" é um registro fixo do sistema e não pode ser excluída.');
        }

        // Bloqueio de integridade: projetos vinculados (preparação para o Épico 4)
        if (
            Schema::hasTable('projetos') &&
            method_exists($agencia, 'projetos') &&
            $agencia->projetos()->exists()
        ) {
            return redirect()->route('agencias.index')
                ->with('error', 'Não é possível excluir esta agência pois existem projetos vinculados a ela.');
        }
        // Bloqueio de integridade: avaliações vinculadas (preparação para o Épico 6)
        if (
            Schema::hasTable('avaliacoes') &&
            method_exists($agencia, 'avaliacoes') &&
            $agencia->avaliacoes()->exists()
        ) {
            return redirect()->route('agencias.index')
                ->with('error', 'Não é possível excluir esta agência pois existem avaliações vinculadas a ela.');
        }

        $agencia->delete();

        return redirect()->route('agencias.index')
            ->with('success', 'Agência de fomento excluída com sucesso!');
    }
}
