<?php

use App\Http\Controllers\AdminUsuarioController;
use App\Http\Controllers\AgenciaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\DisciplinaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjetoController;
use Illuminate\Support\Facades\Route;

// Rotas Públicas / Home
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/teste-flash', [Controller::class, 'testFlash'])->name('test.flash');
Route::redirect('/home', '/');

// Rotas de Autenticação (Apenas Convidados / Visitantes)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

});

// Rotas Autenticadas
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    // Rotas de Perfil
    Route::get('/meu-perfil', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::put('/meu-perfil', [ProfileController::class, 'update'])
        ->name('profile.update');

    // Rotas de Gestão Administrativa de Usuários
    Route::get('/usuarios', [AdminUsuarioController::class, 'showUsers'])
        ->name('usuarios.showUsers');
    Route::put('/usuarios/{usuario}', [AdminUsuarioController::class, 'update'])
        ->name('usuarios.update');

    // Rotas de Gestão Administrativa de Campi
    Route::resource('campuses', CampusController::class)
        ->parameters([
            'campuses' => 'campus',
        ])
        ->only(['index', 'store', 'update', 'destroy']);

    // Rotas de Gestão Administrativa de Cursos
    Route::resource('cursos', CursoController::class)
        ->parameters(['cursos' => 'curso'])
        ->only(['index', 'store', 'update', 'destroy']);

    // Rotas de Gestão Administrativa de Departamentos / Laboratórios
    Route::resource('departamentos', DepartamentoController::class)
        ->parameters(['departamentos' => 'departamento'])
        ->only(['index', 'store', 'update', 'destroy']);

    // Rotas de Gestão Administrativa de Disciplinas / Períodos (PINC)
    Route::resource('disciplinas', DisciplinaController::class)
        ->parameters(['disciplinas' => 'disciplina'])
        ->only(['index', 'store', 'update', 'destroy']);

    // Rotas de Gestão Administrativa de Agências de Fomento
    Route::resource('agencias', AgenciaController::class)
        ->parameters(['agencias' => 'agencia'])
        ->only(['index', 'store', 'update', 'destroy']);

    // Rotas de Gestão Administrativa de Projetos
    Route::resource('projetos', ProjetoController::class)
    ->only(['create', 'store', 'edit', 'update']);
});

Route::post('/impersonar/{usuario}', [ImpersonationController::class, 'start'])->name('impersonate.start');
Route::post('/impersonar-sair', [ImpersonationController::class, 'leave'])->name('impersonate.leave');

// Rota de Dev Switcher (Apenas Ambiente Local)
if (app()->isLocal()) {
    Route::post('dev/login', [AuthController::class, 'devLogin'])->name('dev.login');
}
