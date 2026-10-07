<?php

namespace App\Actions;

use App\Enums\ParticipanteStatusEnum;
use App\Models\Projeto;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class CriarProjetoAction
{
    public function execute(array $dados, Usuario $responsavel): Projeto
    {
        /**
         * O DB::transaction() precisa receber uma função (Closure) para que ele mesmo tenha o controle de:
         * 1. Abrir a transação (BEGIN).
         * 2. Executar a sua função na hora certa.
         * 3. Se der certo, fazer COMMIT.
         * 4. Se der exceção, fazer ROLLBACK.
         */
        // retorna projeto
        return DB::transaction(fn () => $this->criarComParticipante($dados, $responsavel));
    }

    private function criarComParticipante(array $dados, Usuario $responsavel): Projeto
    {
        $dados['responsavel_id'] = $responsavel->id;
        $projeto = Projeto::create($dados); // cria projeto
        $projeto->participantes()->attach( // vincula participante
            $responsavel->id,
            ['flags' => ParticipanteStatusEnum::Ativo->value]
        );

        return $projeto;
    }
}
