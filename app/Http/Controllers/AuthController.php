<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return Inertia::render('auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $cpfLimpo = preg_replace(
            '/\D/',
            '',
            (string) $request->input('cpf')
        );
        $request->merge(['cpf' => $cpfLimpo]);

        $credenciais = $request->validate([
            'cpf' => ['required', 'string', 'size:11'],
            'password' => ['required', 'string'],
        ], [
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.size' => 'O CPF deve conter exatamente 11 dígitos.',
            'password.required' => 'A senha é obrigatória.',
        ]);

        $credenciais['situacao'] = true; // Apenas usuários ativos

        // Auth::attempt -> Verifica se o usuário existe, compara senha e faz o login. Retornando um boolean
        if (! Auth::attempt($credenciais, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'cpf' => 'CPF ou senha incorretos ou usuário inativo.',
            ]);
        }
        // Laravel joga fora o ID antigo e cria um ID criptografado
        $request->session()->regenerate();

        return redirect()
            ->intended(route('home')) // redireciona para url que o usuário tentou acessar antes de logar, caso não exista essa INTENCÇÃO redireciona para a home
            ->with('success', 'Bem-vindo(a) ao PINC, '.Auth::user()->nome.'!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        // Limpa todos os dados armazenados na sessão atual e destrói o arquivo/registro da sessão no servidor.
        $request->session()->invalidate();
        // Gera um novo token CSRF (Cross-Site Request Forgery)
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Sessão encerrada com sucesso.');
    }

    /**
     * Login rápido de 1 clique exclusivo para ambiente de desenvolvimento.
     */
    public function devLogin(Request $request): RedirectResponse
    {
        abort_unless(app()->isLocal(), 403, 'Acesso restrito ao ambiente local.');

        $request->validate([
            'cpf' => ['required', 'string'],
        ]);

        $usuario = Usuario::where('cpf', $request->cpf)->firstOrFail();

        Auth::login($usuario);

        $request->session()->regenerate();

        $perfisNomes = $usuario->perfis->pluck('nome')->implode(', ');

        return redirect()
            ->route('home')
            ->with('success', "Logado via Dev Switcher como {$usuario->nome} [{$perfisNomes}]");
    }
}
