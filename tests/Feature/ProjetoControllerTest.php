<?php

namespace Tests\Feature;

use App\Enums\PerfilEnum;
use App\Models\Departamento;
use App\Models\Projeto;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjetoControllerTest extends TestCase
{
    use RefreshDatabase; // Reseta o banco em memória para cada teste

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PerfilSeeder::class); //Popula a tabela Perfil sempre que um teste é executado
        $this->withoutVite(); // ⬅️ Ignora a verificação do Vite durante a suíte de testes!
    }

    /**
     * Acessa GET /projetos/create, recebe status 200
     * Confirma que o Inertia renderiza projetos/Create com cursos, departamentos e agencias.
     */
    public function test_docente_com_lattes_pode_acessar_pagina_de_criacao() : void
    {
        /** @var Usuario $docente */
        $docente = Usuario::factory()->create([
            'lattes' => 'http://lattes.cnpq.br/1234'
        ]);
        $docente->perfis()->attach(PerfilEnum::Docente->value);

        $response = $this->actingAs($docente) //docente->utilizado para autenticação necessária para conseguir acessar a pagina projetos.create
            ->get(route('projetos.create'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn(Assert $page) => $page
            ->component('projetos/Create')
            ->has('cursos')
            ->has('departamentos')
            ->has('agencias')
        );
    }

    public function test_discente_ou_docente_sem_lattes_recebe_403_ao_acessar_criacao():void
    {
        /** @var Usuario $docente */
        $docente = Usuario::factory()->create();
        $docente->perfis()->attach(PerfilEnum::Docente->value);

        /** @var Usuario $dicente */
        $dicente = Usuario::factory()->create();
        $dicente->perfis()->attach(PerfilEnum::Discente->value);

        $responseDocente = $this->actingAs($docente)->get(route('projetos.create'));
        $responseDicente = $this->actingAs($dicente)->get(route('projetos.create'));

        $responseDocente->assertForbidden();//atalho para assertStatus(403)
        $responseDicente->assertStatus(403);

    }
    
    /**
     * Envia POST /projetos com payload válido, é redirecionado com sucesso e o banco confirma o registro com o responsavel_id igual ao docente logado (assertDatabaseHas).
     */
    public function test_docente_pode_cadastrar_projeto_com_sucesso():void 
    {
        /** @var Usuario $docente */
        $docente = Usuario::factory()->create([
            'lattes' => 'http://lattes.cnpq.br/1234'
        ]);
        $docente->perfis()->attach(PerfilEnum::Docente->value);

        $departamento = Departamento::factory()->create();

        $payload = [
            'titulo' => 'Algoritmos Genéticos em Otimização',
            'assunto' => 'Inteligência Artificial',
            'descricao' => 'Descrição detalhada sobre a pesquisa.',
            'vagas' => 3,
            'departamento_id' => $departamento->id,
            'agencia_id' => null,
        ];

        $response = $this->actingAs($docente)->post(
            route('projetos.store'), 
            $payload
        );

        //Verifica se a resposta foi um redirecionamento HTTP (status 302).
        $response->assertRedirect(route('home'));
        //Valida se a Flash Message enviada no ->with('chave', 'valor') do Controller retorna 'succes'
        $response->assertSessionHas('success'); 

        //Roda uma query SQL direta na tabela com os critérios que você passou no array:
        $this->assertDatabaseHas('projetos', [
            'titulo' => 'Algoritmos Genéticos em Otimização',
            'responsavel_id' => $docente->id, // Comprova que o backend amarrou o usuário logado!
            'departamento_id' => $departamento->id,
            'vagas' => 3,
        ]);
    }

    /**
     * Envia POST /projetos com vagas => 0 e confirma erro de validação na sessão (assertSessionHasErrors(['vagas'])
     */
    public function test_validacao_rejeita_vagas_menor_que_um(): void
    {
        /** @var Usuario $docente */
        $docente = Usuario::factory()->create([
            'lattes' => 'http://lattes.cnpq.br/1234'
        ]);
        $docente->perfis()->attach(PerfilEnum::Docente->value);

        $departamento = Departamento::factory()->create();

        $payload = [
            'titulo' => 'Algoritmos Genéticos em Otimização',
            'assunto' => 'Inteligência Artificial',
            'descricao' => 'Descrição detalhada sobre a pesquisa.',
            'vagas' => 0,
            'departamento_id' => $departamento->id,
            'agencia_id' => null,
        ];

        $response = $this->actingAs($docente)->post(
            route('projetos.store'), 
            $payload
        );

        //Verifica se a resposta foi um redirecionamento HTTP (status 302).
        $response->assertSessionHasErrors(['vagas']);//Esse campo é descrito no ProjetoRequest
    }

    /**
     * Abre a tela GET /projetos/{id}/edit e envia PUT /projetos/{id} com novos dados, confirmando a atualização no banco.
     */
    public function test_docente_responsavel_pode_abrir_e_atualizar_seu_projeto(): void
    {
         /** @var Usuario $docente */
        $docente = Usuario::factory()->create([
            'lattes' => 'http://lattes.cnpq.br/1234'
        ]);
        $docente->perfis()->attach(PerfilEnum::Docente->value);

        /** @var Projeto $projeto */
        $projeto = Projeto::factory()->create([
            'responsavel_id' => $docente->id,
        ]);

        $response = $this->actingAs($docente)->get(route('projetos.edit', $projeto));
        $response->assertStatus(200);

        $response->assertInertia(fn(Assert $page) => $page
            ->component('projetos/Edit')
            ->has('cursos')
            ->has('projeto')
            ->has('departamentos')
            ->has('agencias')
        );

        $payloadAtualizado = [
            'titulo' => 'Novo titulo atualizado',
            'assunto' => $projeto->assunto,
            'descricao' => $projeto->descricao,
            'vagas' => 5,
            'departamento_id' => $projeto->departamento_id,
            'agencia_id' => null,
        ];

        $responseEdit = $this->actingAs($docente)->put(
            route('projetos.update', $projeto), 
            $payloadAtualizado
        );

        //Verifica se a resposta foi um redirecionamento HTTP (status 302).
        $responseEdit->assertRedirect(route('home'));
        //Valida se a Flash Message enviada no ->with('chave', 'valor') do Controller retorna 'succes'
        $responseEdit->assertSessionHas('success'); 

        $this->assertDatabaseHas('projetos', [
            'id' => $projeto->id,
            'titulo' => 'Novo titulo atualizado',
            'responsavel_id' => $docente->id, // Comprova que o backend amarrou o usuário logado!
            'departamento_id' => $projeto->departamento_id,
            'vagas' => 5,
        ]);
    }
    /**
     * Outro docente tenta acessar a edição ou enviar PUT e recebe 403 (assertForbidden()).
     */
    public function test_outro_docente_recebe_403_ao_tentar_editar_projeto_alheio(): void 
    {
        /** @var Usuario $docenteDono */
        $docenteDono = Usuario::factory()->create([
            'lattes' => 'http://lattes.cnpq.br/1234'
        ]);
        $docenteDono->perfis()->attach(PerfilEnum::Docente->value);

        $projeto = Projeto::factory()->create(
            ['responsavel_id'=>$docenteDono->id]
        );

        /** @var Usuario $outroDocente */
        $outroDocente = Usuario::factory()->create([
            'lattes' => 'http://lattes.cnpq.br/1234'
        ]);
        $outroDocente->perfis()->attach(PerfilEnum::Docente->value);

        $this->actingAs($outroDocente)
            ->get(route('projetos.edit', $projeto))
            ->assertForbidden();

        $this->actingAs($outroDocente)
            ->put(route('projetos.update', $projeto), [
                'titulo' => 'Tentativa Indevida de Edição',
                'assunto' => $projeto->assunto,
                'descricao' => $projeto->descricao,
                'vagas' => 2,
                'departamento_id' => $projeto->departamento_id,
            ])
            ->assertForbidden();
    }
}
