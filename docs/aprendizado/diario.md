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

