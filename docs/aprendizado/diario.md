# Diário de Aprendizagem - PINC

Registro de evolução técnica, lacunas identificadas e fila de repetição espaçada.

---

## E4-T1: Modelagem da Tabela `projetos` e Relacionamento do Responsável
- **Data de Conclusão:** 01/10/2026
- **Status:** Concluído com sucesso (3 testes passando, 9 asserções)
- **Conceitos Praticados:**
  - Criação de migration com tipos estritos (`unsignedInteger`, `text`, `string(300)`, `string(500)`), soft deletes e integridade relacional (`restrictOnDelete`).
  - Ordem correta de modificadores de coluna e chave estrangeira (`nullable()->constrained()`).
  - Model Eloquent com `$fillable`, `$casts` e traits (`SoftDeletes`, `HasFactory`).
  - Regra de Ouro dos relacionamentos: quem guarda a chave estrangeira na própria tabela usa `belongsTo`; o inverso usa `hasMany`.
  - Relacionamentos com suporte a registros excluídos historicamente via `withTrashed()`.
  - Local Scopes (`scopeAtivos`, `scopeArquivados`) vs Global Scopes (como o `SoftDeletingScope`).
  - Factories conectadas entre models (`ProjetoFactory` gerando `Departamento` e `Usuario`) e estados de factory (`arquivado()`).
  - Primeiro teste automatizado com PHPUnit e `RefreshDatabase`.
- **Lacunas Superadas:**
  - Diferença entre `HasMany` e `BelongsTo` (quem guarda a FK).
  - Como funcionam e para que servem os Local Scopes (centralização de regras de consulta sem duplicação em controllers).
- **Fila de Revisão Espaçada:**
  - `belongsTo` vs `hasMany` e parâmetros explícitos (D+1: 02/10/2026, D+7: 08/10/2026).
  - Local Scopes e manipulação de `$query` (D+1: 02/10/2026, D+7: 08/10/2026, D+21: 22/10/2026).
  - Asserções no PHPUnit (`assertDatabaseHas`, `assertInstanceOf`, `assertCount`, `assertSoftDeleted`) (D+1: 02/10/2026).

---

## E4-T2: Regras de Negócio de Criação de Projeto (Policy docente + validação de link Lattes)
- **Data de Conclusão:** 03/10/2026
- **Status:** Concluído com sucesso (4 testes passando, 4 asserções)
- **Conceitos Praticados:**
  - Policy Auto-Discovery no Laravel 11/12: convenção de nomenclatura (`App\Policies\{Model}Policy`) eliminando a necessidade de registro manual.
  - Método `before()` como interceptador geral de permissões para perfis privilegiados (`Root`, `Administrador`), compreendendo a semântica de retorno (`true` para bypass, `null` para delegar ao método).
  - Objeto de resposta `Illuminate\Auth\Access\Response` com `Response::allow()` e `Response::deny('...')` fornecendo mensagens amigáveis de autorização (HTTP 403).
  - Helper `blank()` do Laravel para validação abrangente de ausência de dados (`null`, string vazia e string contendo apenas espaços em branco via `trim`).
  - PHPUnit Feature Test para Policies com `RefreshDatabase`, seeding de dependências no `setUp()` (`PerfilSeeder`) e testes de autorização com `$user->can(...)` e `Gate::inspect(...)`.
- **Lacunas Superadas:**
  - Diferença arquitetural entre Middleware (camada HTTP/rede) e Policy (camada de domínio/modelo).
  - Comportamento de truthy em PHP e a lógica de negação para validações de bloqueio.
  - Mecânica do `before()`: nunca retornar `false` se o objetivo for apenas delegar a decisão para o método da Policy.
- **Fila de Revisão Espaçada:**
  - Policy Auto-Discovery e `Response::deny()` vs `bool` (D+1: 04/10/2026, D+7: 10/10/2026).
  - Semântica de retorno do método `before()` (`true` vs `null` vs `false`) (D+1: 04/10/2026, D+7: 10/10/2026, D+21: 24/10/2026).
  - Testes de autorização com `$user->can()` e `Gate::inspect()` (D+1: 04/10/2026, D+7: 10/10/2026).

---

## E4-T3: Formulário de Cadastro e Edição de Projetos (Dropdown encadeado Curso → Depto, validação de vagas)
- **Data de Conclusão:** 06/10/2026
- **Status:** Concluído com sucesso (6 testes passando, 19 asserções)
- **Conceitos Praticados:**
  - FormRequest (`ProjetoRequest`) com regras de domínio (`min:1`, `withoutTrashed()`) e mensagens personalizadas em português.
  - Separação RESTful no Inertia: rotas de tela (`create`, `edit`) com GET vs rotas de mutação (`store`, `update`) com POST/PUT.
  - Segurança contra IDOR: fixação do `responsavel_id` pelo usuário autenticado no backend (`$request->user()->id`), sem confiar no payload do cliente.
  - Eager loading relacional aninhado (`$projeto->load('departamento.curso')`) para resolver dependências em cascata no frontend.
  - Reatividade encadeada no Vue 3 com PrimeVue v4: uso de `computed` para filtrar departamentos conforme o `form.curso_id` e reset de seleção via evento `@change`.
  - Arquitetura de componentes limpa (Pattern de Partials): extração de `ProjetoForm.vue` mantendo `Create.vue` e `Edit.vue` como cascas modulares e sem duplicação de template.
  - Testes de Feature completos no PHPUnit: `actingAs()`, teste de contrato com `assertInertia()`, validação de erros de sessão (`assertSessionHasErrors(['vagas'])`), redirecionamentos, mensagens flash e `$this->withoutVite()`.
- **Lacunas Superadas:**
  - Por que FormRequests só devem ser injetados em mutações (POST/PUT) e não em requisições de exibição de tela (GET).
  - O papel do `actingAs()` na simulação da sessão autenticada para middlewares, policies e `$request->user()`.
  - Como o `computed` do Vue rastreia dependências reativas automaticamente.
  - Necessidade de `parent::setUp()` e `$this->seed(PerfilSeeder::class)` para tabelas de catálogo com `RefreshDatabase`.
- **Fila de Revisão Espaçada:**
  - Dropdown encadeado no Vue 3 com `computed` e `useForm` (D+1: 07/10/2026, D+7: 13/10/2026).
  - Proteção contra IDOR no backend e autorização com Policies (D+1: 07/10/2026, D+7: 13/10/2026).
  - Asserções de teste no Inertia (`assertInertia`, `assertSessionHasErrors`) e `$this->withoutVite()` (D+1: 07/10/2026, D+7: 13/10/2026, D+21: 27/10/2026).

