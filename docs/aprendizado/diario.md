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

---

## E4-T4: Modelagem da Pivô `projeto_usuario` e Enum `ParticipanteStatusEnum`
- **Data de Conclusão:** 07/10/2026
- **Status:** Concluído com sucesso (4 testes passando, 6 asserções no pivot test; 17 testes passando na suíte de projetos)
- **Conceitos Praticados:**
  - Backed Enum nativo do PHP 8.3 (`ParticipanteStatusEnum: int`) com ciclo de vida acadêmico (`Ativo`, `Entrando`, `Saindo`, `Historico`), `match ($this)` para rótulos legíveis e severidades visuais para o PrimeVue.
  - Comportamento de Backed Enums: diferença entre `from()` (estrito, lança `ValueError`) e `tryFrom()` (retorna `null` de forma segura).
  - Migration de tabela pivô intermediária N:N (`projeto_usuario`) com chaves estrangeiras com `cascadeOnDelete()`, `timestamps()` e índice de unicidade composto `$table->unique(['projeto_id', 'usuario_id'])`.
  - Distinção essencial entre Soft Delete (`UPDATE` no banco) e Hard Delete (`DELETE` físico), entendendo por que o soft delete do projeto pai não aciona o `ON DELETE CASCADE` do motor do banco.
  - Relacionamentos N:N no Eloquent via `BelongsToMany` em ambos os lados (`Projeto` e `Usuario`), ordem de chaves (`$foreignPivotKey`, `$relatedPivotKey`) e regras da convenção automática do Laravel.
  - Mecanismo do método mágico `__get()` do PHP permitindo acessar métodos de relação como propriedades dinâmicas com cache em `Collection`.
  - Como o Eloquent lida com atributos intermediários da tabela associativa via sub-objeto `$model->pivot` e a necessidade de `withPivot('flags')` e `withTimestamps()`.
  - Testes de Feature automatizados no PHPUnit com `RefreshDatabase`, obrigatoriedade do `parent::setUp()`, testes de bidirecionalidade com Collection (`contains()`), interceptação de exceções de integridade do banco (`expectException(QueryException::class)`) e asserções direcionadas com `assertDatabaseMissing()`.
- **Lacunas Superadas:**
  - A clássica inversão de chaves no `belongsToMany` em classes relacionadas (quem aponta para MIM vs quem aponta para o OUTRO).
  - Como a relação N:N é processada por baixo dos panos via SQL `INNER JOIN` e por que colunas extras da pivô exigem o `withPivot`.
  - Por que `parent::setUp()` deve ser chamado antes de qualquer operação ao sobrescrever o `setUp` em testes do Laravel.
- **Aprofundamento Técnico: Como o Eloquent Lida com N:N e o `withPivot`:**
  - **Mecanismo Relacional (SQL):** Ao executar `$projeto->participantes`, o Eloquent monta um `INNER JOIN` entre a tabela de destino e a intermediária:
    ```sql
    SELECT usuarios.*, projeto_usuario.projeto_id, projeto_usuario.usuario_id, projeto_usuario.flags
    FROM usuarios
    INNER JOIN projeto_usuario ON usuarios.id = projeto_usuario.usuario_id
    WHERE projeto_usuario.projeto_id = 1;
    ```
  - **Hidratação do Model e o Objeto `pivot`:** As colunas da tabela principal (`usuarios`) tornam-se atributos nativos do model (`$usuario->nome`). Os atributos originários da tabela intermediária (`projeto_usuario`) são alocados dentro de uma instância auxiliar acoplada: `$usuario->pivot`.
  - **Por que `withPivot('flags')` é indispensável:** Por economia de memória e processamento, o Eloquent por padrão copia para o objeto `pivot` **apenas as duas chaves estrangeiras** (`projeto_id` e `usuario_id`), descartando todas as outras colunas. O método `->withPivot('flags')` comanda o Eloquent a não descartar e popular a coluna no objeto (`$usuario->pivot->flags`). Sem essa declaração, a tentativa de leitura retorna `null` silenciosamente em tempo de execução.
- **Fila de Revisão Espaçada:**
  - Convenções e ordem de chaves do `belongsToMany` e funcionamento do `withPivot` (D+1: 08/10/2026, D+7: 14/10/2026, D+21: 28/10/2026).
  - Backed Enums (`from` vs `tryFrom`) e teste de exceções no PHPUnit com `expectException` (D+1: 08/10/2026, D+7: 14/10/2026).
  - Índice de unicidade composto em tabelas pivô vs índices simples (D+1: 08/10/2026, D+7: 14/10/2026, D+21: 28/10/2026).



---

## E4-T5: Associação Automática do Docente Responsável como Participante
- **Data de Conclusão:** 07/10/2026
- **Status:** Concluído com sucesso (7 testes passando, 40 asserções no `ProjetoControllerTest`; 18 testes passando no total de Projetos)
- **Conceitos Praticados:**
  - Padrão **Domain Action** (Action Pattern) para mutações isoladas de domínio (SRP - Single Responsibility Principle).
  - Injeção de Dependências no Laravel via **Method Injection** em controllers resolvido pelo Service Container via Reflection API.
  - Princípios ACID e **Transações de Banco de Dados** com `DB::transaction()` no Laravel: abertura com `BEGIN`, confirmação com `COMMIT` e reversão automática com `ROLLBACK` sob qualquer `Throwable`.
  - Diferença entre passar o resultado de uma função avaliada imediatamente (`DB::transaction($this->...)`) versus passar uma Closure/Arrow Function para execução diferida controlada (`fn () => ...`).
  - Retorno de métodos no Eloquent: compreensão de que `$relationship->attach(...)` retorna `void`/`null`, sendo necessário retornar explicitamente a instância criada (`$projeto`).
  - Interceptação de queries SQL em testes com `DB::listen()` para simular falhas no banco e comprovar rollback atômico.
  - Leitura e interpretação de **Stack Traces** no PHPUnit (da origem no fundo da pilha até a explosão no topo) e como filtros de queries (`insert` vs `select`) evitam efeitos colaterais em asserções de verificação (`assertDatabaseMissing`).
- **Lacunas Superadas:**
  - Por que `DB::transaction()` exige uma Closure/Callable em vez da chamada direta do método.
  - A armadilha de retornar o resultado do `attach()` (que devolve `null` quebrando o tipo de retorno estrito `: Projeto`).
  - Como simular acidentes de infraestrutura em testes com `DB::listen()` e por que filtrar apenas comandos `INSERT` para não quebrar os `SELECT` de asserções subsequentes.
- **Fila de Revisão Espaçada:**
  - Mecânica interna do `DB::transaction()` e rollback automático (D+1: 08/10/2026, D+7: 14/10/2026, D+21: 28/10/2026).
  - Injeção de Dependência no Laravel: Method Injection vs Constructor Injection (D+1: 08/10/2026, D+7: 14/10/2026).
  - Interceptação de queries com `DB::listen` e leitura de Stack Traces no PHPUnit (D+1: 08/10/2026, D+7: 14/10/2026, D+21: 28/10/2026).

---

## E4-T6: Catálogo Público de Projetos na Home
- **Data de Conclusão:** 08/10/2026
- **Status:** Concluído com sucesso (3 testes passando, 38 asserções no `HomeControllerTest`; 21 testes passando no total de Projetos)
- **Conceitos Praticados & Fundamentações:**
  - **CQS (Command-Query Separation) no Laravel:**
    - *Commands (Mutações/Escrita):* Devem morar em **Actions** (ex: `CriarProjetoAction`). Possuem efeitos colaterais, transações (`DB::transaction`) e disparam eventos. Evita o anti-padrão de *Fat Models*.
    - *Queries (Consultas/Leitura):* Devem morar em **Local Scopes / Custom Query Builders** no Model (ex: `scopeAtivos`, `scopeBuscar`). Traduzem o vocabulário de negócio para SQL sem alterar estado.
  - **Active Record vs Model Anêmico (Ponte Angular):**
    - No Angular/TypeScript, models são interfaces ou DTOs sem lógica interna (anêmicos).
    - No Eloquent (Active Record), o Model detém tanto os dados e relacionamentos quanto as regras de consulta e o comportamento da entidade.
  - **Mecânica do Builder e Método Mágico `__call()`:**
    - Ao chamar `Projeto::ativos()->buscar(...)`, o Eloquent instancia `Illuminate\Database\Eloquent\Builder`.
    - Chamadas a escopos são interceptadas via `__call()`, que executa o método do Model passando o `$builder` por referência.
    - Mesmo que o escopo tenha retorno `void`, o Builder do Laravel sempre retorna a si mesmo (`$this`), viabilizando o encadeamento fluente até a execução de um método terminal (`paginate()`, `get()`, `first()`).
  - **Eager Loading vs Lazy Loading (Solução do N+1):**
    - *Lazy Loading (padrão):* Dispara 1 query para o pai e +1 query para cada filho acessado no loop/card ($1 + N$ queries), gerando gargalo severo de banco.
    - *Eager Loading (`with(['responsavel', 'departamento'])`):* Realiza consultas prévias agrupadas com `WHERE IN (...)` e hidrata as relações em memória, fixando a execução em 3 queries independentemente do volume de registros na página.
  - **Precedência de Operadores SQL (`AND` vs `OR`) no Eloquent:**
    - O operador `AND` possui precedência sobre `OR`. Para evitar que condições alternativas quebrem filtros anteriores (como `scopeAtivos`), é obrigatório agrupar os `OR` dentro de uma closure `$query->where(function ($sub) { ... })`.
  - **Reatividade com Inertia no Frontend:**
    - Uso de `router.get('/', params, { preserveState: true, replace: true })` para filtrar a página mantendo foco do input e sem poluir o histórico do navegador.
    - Diferença de indexação: PrimeVue `Paginator` emite evento 0-indexed (`event.page + 1`), enquanto o Laravel Paginator é 1-indexed.
- **Lacunas Superadas:**
  - Compreensão de por que métodos de scope com retorno `void` permitem encadear métodos fluentes via `__call()`.
  - Diferença vital entre Eager Loading (`with`) e Lazy Loading.
  - Como a precedência de operadores SQL exige envelopamento de `orWhere` em closures para não quebrar o `scopeAtivos`.
  - Como passar query parameters em testes de Feature no Laravel via helper `route('home', ['busca' => '...'])`.
- **Fila de Revisão Espaçada:**
  - CQS e Scopes vs Actions (D+1: 09/10/2026, D+7: 15/10/2026, D+21: 29/10/2026).
  - Eager Loading com `with()` e prevenção de N+1 (D+1: 09/10/2026, D+7: 15/10/2026, D+21: 29/10/2026).
  - `preserveState` e `replace` no Inertia vs SPA tradicional (D+1: 09/10/2026, D+7: 15/10/2026).
  - Precedência de operadores SQL e agrupamento de `where` com closure (D+1: 09/10/2026, D+7: 15/10/2026).
