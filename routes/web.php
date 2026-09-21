<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Rotas Públicas / Home
Route::get('/', [Controller::class, 'index'])->name('home');
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
    Route::get('/meu-perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/meu-perfil', [ProfileController::class, 'update'])->name('profile.update');
});

Route::post('/impersonar/{usuario}', [ImpersonationController::class, 'start'])->name('impersonate.start');
Route::post('/impersonar-sair', [ImpersonationController::class, 'leave'])->name('impersonate.leave');

// Rota de Dev Switcher (Apenas Ambiente Local)
if (app()->isLocal()) {
    Route::post('dev/login', [AuthController::class, 'devLogin'])->name('dev.login');
}

