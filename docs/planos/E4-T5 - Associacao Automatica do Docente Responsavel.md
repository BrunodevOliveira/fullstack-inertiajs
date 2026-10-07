# E4-T5 - Associação Automática do Docente Responsável

## Objetivo de aprendizagem (o que vou saber explicar ao final)
- Como aplicar o padrão **Domain Action** (Action Pattern) no Laravel para isolar operações de negócio complexas fora de Controllers e Models (princípio de Responsabilidade Única - SRP).
- Como funcionam **Transações de Banco de Dados** com `DB::transaction()` no Laravel: conceitos ACID, atomicidade ("tudo ou nada") e mecanismo de rollback automático em caso de exceções.
- Como orquestrar relacionamentos Eloquent (`belongsTo` e `belongsToMany::attach`) de maneira coordenada e segura dentro de uma transação.
- Como simular falhas e exceções em testes automatizados do PHPUnit com `RefreshDatabase` para provar que o rollback realmente desfaz todas as alterações quando algo quebra.

## Ponte Angular → Laravel/Vue (analogias e falsos amigos)
- **Domain Action vs Command/UseCase/Service no Angular:** No Angular e na Clean Architecture, você evita injetar regras de negócio complexas ou múltiplas mutações dentro de Componentes. Você cria um Use Case ou Command específico (ex.: `CreateProjectUseCase.execute()`). No Laravel moderno, chamamos isso de **Action Class** (`CriarProjetoAction`), uma classe PHP simples e focada, tipicamente com um método público `execute()`, que orquestra toda a mutação de domínio.
- **`DB::transaction()` vs Chamadas HTTP Sequenciais:** No frontend/Angular, se você dispara duas requisições consecutivas (`createProject()` e depois `addMember()`) e a segunda falha, o cliente precisa implementar lógica manual de compensação/cancelamento. No backend com banco relacional (MySQL), temos transações ACID nativas (`BEGIN`, `COMMIT`, `ROLLBACK`). O helper `DB::transaction(fn () => ...)` abre a transação, commita se o callback terminar sem erros, e dispara rollback imediato se qualquer `Throwable`/`Exception` for lançada.
  - **Falso Amigo (Transações e Silent Fails):** Se você colocar um `try/catch` dentro do callback do `DB::transaction()` e "engolir" a exceção (sem relançá-la com `throw $e`), o Laravel entenderá que o bloco executou com sucesso e fará o `COMMIT`! O `DB::transaction` só cancela as operações se a exceção borbulhar para fora da Closure.

## Contrato de comportamento (linguagem natural)
- **Caminho Feliz:**
  - O docente submete dados válidos para criação de projeto via `POST /projetos`.
  - O `ProjetoController@store` autoriza a requisição via `Gate::authorize('create', Projeto::class)`, valida os dados via `ProjetoRequest` e repassa a responsabilidade para `CriarProjetoAction::execute($dados, $usuario)`.
  - A action executa dentro de um `DB::transaction`:
    1. Cria o registro na tabela `projetos` com `responsavel_id` igual ao ID do usuário autenticado.
    2. Vincula o usuário na tabela pivô `projeto_usuario` através de `$projeto->participantes()->attach(...)` com `flags = ParticipanteStatusEnum::Ativo->value`.
  - A transação é commitada e o `Projeto` recém-criado é retornado pela Action.
  - O controller redireciona com mensagem flash de sucesso.
- **Casos Adversos:**
  - Se a inserção na tabela pivô `projeto_usuario` falhar (erro de banco, exceção forçada em teste, constraint violada):
    - A transação é abortada imediatamente (`ROLLBACK`).
    - O registro do projeto que havia acabado de ser inserido é desfeito no banco de dados.
    - O banco de dados permanece em estado íntegro (`assertDatabaseMissing('projetos', ...)` e `assertDatabaseMissing('projeto_usuario', ...)`).

## Partes (decomposição atômica)

### Parte 1: Criação da Domain Action `CriarProjetoAction`
- **Arquivos afetados:** `app/Actions/CriarProjetoAction.php`
- **Conceito-chave:** Classe POPO (Plain Old PHP Object) com método `execute(array $dados, Usuario $responsavel): Projeto`. Uso de `DB::transaction()` envolvendo a criação do modelo `Projeto` e a inserção na tabela pivô via `$projeto->participantes()->attach()`.
- **Como verificar:** Teste no Tinker simulando a criação de um projeto com usuário e verificando ambos os registros no banco.
- **Pergunta de checagem:** O que acontece se uma exceção for lançada dentro da Closure do `DB::transaction()`? E se colocarmos um `try/catch` que não relança a exceção?

### Parte 2: Refatoração do `ProjetoController@store`
- **Arquivos afetados:** `app/Http/Controllers/ProjetoController.php`
- **Conceito-chave:** Substituição da criação direta (`Projeto::create`) pela delegação para a `CriarProjetoAction` injetada via method injection ou chamada direta.
- **Como verificar:** Submeter o formulário de criação de projeto pela aplicação ou rodar os testes existentes de `ProjetoControllerTest`.
- **Pergunta de checagem:** Por que é vantajoso manter o controller "magro" (Thin Controller) delegando a criação atômica para uma Action?

### Parte 3: Testes Automatizados no PHPUnit
- **Arquivos afetados:** `tests/Feature/ProjetoControllerTest.php`
- **Conceito-chave:**
  - Teste do caminho feliz: verificar persistência na tabela `projetos` E na tabela pivô `projeto_usuario` com a flag correta.
  - Teste de atomicidade/rollback: simular ou forçar uma falha na etapa de vinculação e verificar que nenhum registro residual ficou na tabela `projetos`.
- **Como verificar:** `php artisan test --compact --filter=ProjetoControllerTest`.
- **Pergunta de checagem:** Por que testar o cenário de falha com rollback é tão importante quanto testar o caminho feliz?

## Fora de escopo
- Listagem e busca de projetos com filtros no catálogo (tarefa E4-T6).
- Indicação de novos alunos e ciclo de participantes (Épico 5).
- Desligamento de participantes ou substituição de coordenador (Épico 5).

## Fechamento (checklist de entendimento)
- [x] **Explicar:** O conceito de Domain Action e por que extrair lógica de mutação dos controllers.
- [x] **Explicar:** Como o `DB::transaction()` garante atomicidade e como funciona o mecanismo de rollback.
- [x] **Tracear:** O fluxo completo desde a submissão do formulário no frontend até o commit/rollback no MySQL.
- [x] **Reconstruir:** Explicar a estrutura da Action e como configurar uma transação atômica sem auxílio de IA.

