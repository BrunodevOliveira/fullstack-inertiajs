<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn () => $request->user() ? [
                    'id' => $request->user()->id,
                    'nome' => $request->user()->nome,
                    'email' => $request->user()->email,
                    'cpf' => $request->user()->cpf,
                    'perfis' => $request->user()->perfis->map(fn ($p) => [
                        'id' => $p->id,
                        'nome' => $p->nome,
                    ]),
                ] : null,
            ],
            'impersonator' => fn () => $request->session()->has('impersonator_id') ? [
                'id' => $request->session()->get('impersonator_id'),
                'nome' => Usuario::find($request->session()->get('impersonator_id'))?->nome,
            ] : null,
            'is_local' => app()->isLocal(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
                'warn' => fn () => $request->session()->get('warn'),
            ],
        ];
    }
}
