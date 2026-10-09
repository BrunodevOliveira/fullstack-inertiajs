<?php

namespace Tests\Feature;

use App\Models\Projeto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_pode_visualizar_catalogo_de_projetos_ativos(): void
    {
        Projeto::factory()->count(6)->create();

        $response = $this->withoutVite()->get(route('home'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Home')
                ->has('projetos.data', 6)
                ->where('filters.busca', '')
        );
    }

    public function test_visitante_pode_filtrar_projetos_por_busca_textual(): void
    {
        // Arrange
        Projeto::factory()->create(['titulo' => 'Pesquisa sobre Robótica']);
        Projeto::factory()->create(['titulo' => 'Estudo de Literatura']);

        // Act
        $response = $this->withoutVite()->get(route('home', ['busca' => 'Robótica']));

        // Assert
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Home')
                ->has('projetos.data', 1)
                ->where('filters.busca', 'Robótica')
                ->where('projetos.data.0.titulo', 'Pesquisa sobre Robótica')
        );
    }

    public function test_projetos_arquivados_ou_excluidos_nao_sao_exibidos_no_catalogo(): void
    {
        Projeto::factory()->create(['titulo' => 'Projeto Ativo']);
        Projeto::factory()->arquivado()->create(['titulo' => 'Projeto Arquivado']);

        $excluido = Projeto::factory()->create(['titulo' => 'Projeto Excluido']);
        $excluido->delete();

        $response = $this->withoutVite()->get(route('home'));

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Home')
                ->has('projetos.data', 1)
                ->where('filters.busca', '')
                ->where('projetos.data.0.titulo', 'Projeto Ativo')
        );
    }
}
