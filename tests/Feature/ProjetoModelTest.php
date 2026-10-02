<?php

namespace Tests\Feature;

use App\Models\Agencia;
use App\Models\Departamento;
use App\Models\Projeto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjetoModelTest extends TestCase
{
    use RefreshDatabase; // Reseta o banco em memória para cada teste

    public function test_pode_criar_projeto_com_seus_relacionamentos(): void
    {
        $agencia = Agencia::factory()->create();
        $projeto = Projeto::factory()->create([
            'agencia_id' => $agencia->id,
        ]);
        $this->assertDatabaseHas('projetos', ['id' => $projeto->id]);
        $this->assertInstanceOf(Usuario::class, $projeto->responsavel);
        $this->assertInstanceOf(Departamento::class, $projeto->departamento);
        $this->assertInstanceOf(Agencia::class, $projeto->agencia);
    }

    public function test_escopos_ativos_e_arquivados_filtram_corretamente(): void
    {
        // Cria 2 ativos e 1 arquivado
        Projeto::factory()->count(2)->create(['arquivado' => false]);
        Projeto::factory()->arquivado()->create();
        $this->assertCount(2, Projeto::ativos()->get());
        $this->assertCount(1, Projeto::arquivados()->get());
    }

    public function test_soft_deletes_e_recuperacao_de_responsavel_excluido(): void
    {
        $projeto = Projeto::factory()->create();
        $responsavel = $projeto->responsavel;
        // 1. Testa soft delete do próprio projeto
        $projeto->delete();
        $this->assertSoftDeleted('projetos', ['id' => $projeto->id]);
        // 2. Testa relacionamento com docente desativado (soft delete)
        $responsavel->delete();
        $this->assertSoftDeleted('usuarios', ['id' => $responsavel->id]);
        
        // Graças ao withTrashed(), ainda conseguimos acessar o responsável:
        $this->assertEquals($responsavel->id, $projeto->fresh()->responsavel->id);
    }
}
