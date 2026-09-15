<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

Route::get('/', [Controller::class, 'index'])->name('home');
Route::post('/teste-flash', [Controller::class, 'testFlash'])->name('test.flash');

Route::redirect('/home', '/');
