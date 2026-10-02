# E4-T1 - Modelagem da Tabela projetos e Relacionamento do Responsável

## Objetivo de aprendizagem (o que vou saber explicar ao final)
- Como estruturar chaves estrangeiras com nomes customizados (`responsavel_id` -> `usuarios`) e integridade referencial (`restrictOnDelete`).
- Como implementar e testar Soft Deletes no Eloquent e nas migrations.
- Como encapsular filtros de consulta reutilizáveis através de Local Scopes (`scopeAtivos`, `scopeArquivados`).
- Como definir relacionamentos inversos (`hasMany` vs `belongsTo`) garantindo suporte a registros com exclusão lógica (`withTrashed`).

## Ponte Angular → Laravel/Vue (analogias e falsos amigos)
- **Model Eloquent vs Interfaces/Classes no Angular:** No Angular, uma `interface Projeto` serve estritamente para checagem de tipos em tempo de compilação sem comportamento em runtime. No Laravel, o Model Eloquent é um *Active Record*: ele tem inteligência para consultar o banco, persistir dados, mutar atributos e disparar eventos de ciclo de vida.
- **Local Scopes vs Pipes / Operadores RxJS:** No Angular, para reaproveitar filtros em listagens, você cria um Pipe ou encadeia operadores no `pipe(filter(...))`. No Laravel, um Local Scope opera diretamente na camada do banco de dados (construindo a cláusula SQL `WHERE`), economizando memória no servidor PHP.
- **Falso amigo (Nomenclatura do `belongsTo`):** No Angular, você pode mapear uma propriedade aninhada com qualquer nome no seu DTO. No Eloquent, ao declarar `public function responsavel()`, o Laravel por padrão supõe a classe `Responsavel` e a tabela `responsavels`. Como nossa classe é `Usuario`, precisamos declarar explicitamente: `$this->belongsTo(Usuario::class, 'responsavel_id')`.

## Contrato de comportamento (casos esperados em linguagem natural)
- **Caminho Feliz:**
  - Um projeto é persistido com título, assunto, descrição, vagas, departamento, responsável e agência opcional.
  - `$projeto->responsavel` recupera o `Usuario` vinculado.
  - `$projeto->departamento` e `$projeto->agencia` recuperam os registros relacionados.
  - `Projeto::ativos()` traz projetos com `arquivado = false` ou `arquivado = null`.
  - `Projeto::arquivados()` traz projetos com `arquivado = true`.
  - Ao executar `$projeto->delete()`, o registro sofre soft delete (mantido na tabela com `deleted_at` preenchido).
- **Casos Adversos:**
  - Tentar inserir projeto sem os campos obrigatórios (`titulo`, `assunto`, `descricao`, `departamento_id`, `responsavel_id`) gera erro de integridade de banco de dados.
  - Tentar excluir fisicamente um departamento ou agência vinculado a projetos deve ser bloqueado por restrição de integridade referencial (`restrictOnDelete`).
  - Caso o docente responsável sofra exclusão lógica (soft delete), o projeto ainda deve conseguir carregar seus dados históricos via `withTrashed()`.

## Partes

### Parte 1: Migration da Tabela `projetos`
- **Arquivos afetados:** `database/migrations/xxxx_xx_xx_xxxxxx_create_projetos_table.php`
- **Conceito-chave:** Schema builder, tipos (`string(300)`, `string(500)`, `text`, `unsignedInteger`), foreign keys com `restrictOnDelete()`, `softDeletes()` e índices compostos.
- **Como verificar:** Rodar `php artisan migrate` e inspecionar a estrutura da tabela no banco.
- **Pergunta de checagem:** Por que precisamos passar `'usuarios'` dentro de `constrained('usuarios')` para a coluna `responsavel_id`?

### Parte 2: Model `Projeto` e Escopos
- **Arquivos afetados:** `app/Models/Projeto.php`
- **Conceito-chave:** Traits `HasFactory` e `SoftDeletes`, `$fillable`, `$casts`, relacionamentos `belongsTo` com `withTrashed()`, e escopos locais `scopeAtivos` e `scopeArquivados`.
- **Como verificar:** Rodar consultas via `php artisan tinker` validando o carregamento das relações e o filtro dos escopos.
- **Pergunta de checagem:** Qual a diferença entre filtrar `Projeto::where('arquivado', false)->get()` no controller e centralizar no escopo `Projeto::ativos()->get()`?

### Parte 3: Relacionamentos Inversos e Ajustes nos Models Existentes
- **Arquivos afetados:** `app/Models/Departamento.php`, `app/Models/Agencia.php`, `app/Models/Usuario.php`
- **Conceito-chave:** Adicionar métodos `projetos(): HasMany` e habilitar a trait `SoftDeletes` em `Usuario.php`.
- **Como verificar:** Acessar `$departamento->projetos` e `$agencia->projetos` via Tinker.
- **Pergunta de checagem:** Se um departamento tem muitos projetos, em qual tabela física reside a foreign key?

### Parte 4: Factory e Testes Automatizados (PHPUnit)
- **Arquivos afetados:** `database/factories/ProjetoFactory.php`, `tests/Feature/ProjetoModelTest.php`
- **Conceito-chave:** Criação de factories com estados (`arquivado()`, `comAgencia()`) e suíte de testes cobrindo integridade, escopos e soft delete.
- **Como verificar:** Executar `php artisan test --compact --filter=ProjetoModelTest`.
- **Pergunta de checagem:** O que o método `withTrashed()` faz quando encadeado na definição de um relacionamento `belongsTo`?

## Fora de escopo
- Validações de perfil docente e presença de Lattes (tarefa E4-T2).
- Telas de cadastro, edição e listagem (tarefas E4-T3, E4-T5 e E4-T6).
- Associação de participantes na tabela pivô (E4-T4 e Épico 5).

## Fechamento (checklist de entendimento)
- [ ] **Explicar:** Como o Eloquent resolve nomes de chaves estrangeiras por convenção e quando precisamos intervir.
- [ ] **Tracear:** O caminho da query SQL gerada ao executar `Projeto::ativos()->with('responsavel')->get()`.
- [ ] **Reconstruir:** Criar uma migration com foreign key customizada e soft delete sem consultar documentação externa.
