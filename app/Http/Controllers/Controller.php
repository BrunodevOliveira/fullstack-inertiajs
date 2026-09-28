<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class Controller
{
    public function testFlash(Request $request): RedirectResponse
    {
        $type = $request->input('type', 'success');

        $messages = [
            'success' => 'Operação realizada com sucesso!',
            'error' => 'Ocorreu um erro ao processar sua solicitação.',
            'info' => 'Esta é uma notificação informativa do sistema.',
            'warn' => 'Atenção: verifique os dados antes de prosseguir.',
        ];

        return redirect()->back()->with($type, $messages[$type] ?? 'Notificação de teste.');
    }
}
