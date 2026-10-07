<?php

namespace Tests\Feature;

use App\Enums\ParticipanteStatusEnum;
use App\Models\Projeto;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjetoParticipantePivotTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_pode_ser_vinculado_como_participante_com_status_ativo(): void
    {
        $usuario = Usuario::factory()->create();
        $projeto = Projeto::factory()->create();

        $projeto->participantes()->attach(
            $usuario->id,
            ['flags' => ParticipanteStatusEnum::Ativo->value]
        );

        $this->assertDatabaseHas('projeto_usuario', [
            'usuario_id' => $usuario->id,
            'projeto_id' => $projeto->id,
            'flags' => ParticipanteStatusEnum::Ativo->value,
        ]);

        $this->assertCount(1, $projeto->participantes);

        $this->assertEquals(
            ParticipanteStatusEnum::Ativo->value,
            $projeto->participantes->first()->pivot->flags
        );
    }

    public function test_relacionamento_inverso_retorna_projetos_do_usuario(): void
    {
        $usuario = Usuario::factory()->create();
        $projeto = Projeto::factory()->create();

        $projeto->participantes()->attach(
            $usuario->id,
            ['flags' => ParticipanteStatusEnum::Ativo->value]
        );

        $this->assertTrue($usuario->projetosParticipados->contains($projeto));
    }

    public function test_usuario_nao_pode_ser_vinculado_mais_de_uma_vez_no_mesmo_projeto(): void
    {
        $usuario = Usuario::factory()->create();
        $projeto = Projeto::factory()->create();

        $projeto->participantes()->attach(
            $usuario->id,
            ['flags' => ParticipanteStatusEnum::Ativo->value]
        );

        // Informamos ao PHPUnit que a próxima linha DEVE lançar exceção de banco:
        $this->expectException(QueryException::class);

        $projeto->participantes()->attach(
            $usuario->id,
            ['flags' => ParticipanteStatusEnum::Ativo->value]
        );
    }

    public function test_projeto_apagado_fisicamente_deve_ativar_cascata(): void
    {
        $usuario = Usuario::factory()->create();
        $projeto = Projeto::factory()->create();

        $projeto->participantes()->attach(
            $usuario->id,
            ['flags' => ParticipanteStatusEnum::Ativo->value]
        );

        $projeto->forceDelete();

        // Valida se a linha foi apagada da tabela após forçar o delete do projeto
        $this->assertDatabaseMissing(
            'projeto_usuario',
            ['projeto_id' => $projeto->id] // Critério de busca
        );
    }
}
