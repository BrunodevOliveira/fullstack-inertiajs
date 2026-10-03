<?php

namespace Tests\Feature;

use App\Enums\PerfilEnum;
use App\Models\Projeto;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjetoPolicyTest extends TestCase
{
    use RefreshDatabase; // Reseta o banco em memória para cada teste

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PerfilSeeder::class);
    }

    public function test_docente_com_lattes_pode_criar_projeto(): void
    {
        $docente = Usuario::factory()->create(['lattes' => 'http://lattes.cnpq.br/1234']);
        $docente->perfis()->attach(PerfilEnum::Docente->value);

        $this->assertTrue($docente->can('create', Projeto::class));
    }

    public function test_docente_sem_lattes_nao_pode_criar_projeto(): void
    {
        $docente = Usuario::factory()->create();
        $docente->perfis()->attach(PerfilEnum::Docente->value);
        $this->assertFalse($docente->can('create', Projeto::class));
    }

    public function test_usuario_sem_perfil_docente_nao_pode_criar_projeto(): void
    {
        $aluno = Usuario::factory()->create();
        $aluno->perfis()->attach(PerfilEnum::Discente->value);
        $this->assertFalse($aluno->can('create', Projeto::class));
    }

    public function test_administrador_ou_root_pode_criar_projeto_mesmo_sem_lattes(): void
    {
        $adm = Usuario::factory()->create();
        $adm->perfis()->attach(PerfilEnum::Administrador->value);
        $this->assertTrue($adm->can('create', Projeto::class));
    }
}
