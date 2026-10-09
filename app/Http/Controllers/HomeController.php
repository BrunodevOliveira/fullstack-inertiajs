<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        $projetos = Projeto::ativos()
            ->with(['responsavel', 'departamento'])
            ->buscar($request->input('busca', ''))
            ->latest('id')
            ->paginate(6)
            ->withQueryString();

        return Inertia::render('Home', [
            'title' => 'Página Inicial',
            'projetos' => $projetos,
            'filters' => ['busca' => $request->input('busca', '')],
        ]);
    }
}
