# E4-T4 - Modelagem da Pivô projeto_usuario e Enum ParticipanteStatusEnum

## Objetivo de aprendizagem (o que vou saber explicar ao final)
- Como modelar e tipar um Backed Enum no PHP 8.3+ com valores inteiros e labels descritivos.
- Como criar uma migration para tabela pivô intermediária (`projeto_usuario`) com integridade referencial (`cascadeOnDelete`), timestamps e índice de unicidade composto.
- Como configurar relacionamentos Many-to-Many (`belongsToMany`) no Eloquent com acesso aos dados da pivô (`withPivot`, `withTimestamps`).
- Como escrever testes automatizados no PHPUnit com `RefreshDatabase` garantindo a integridade relacional, unicidade do par `(projeto_id, usuario_id)` e leitura correta da pivô.

## Ponte Angular → Laravel/Vue (analogias e falsos amigos)
- **Backed Enum vs TypeScript Enum:** No Angular/TypeScript, você declara `export enum ParticipanteStatus { Ativo = 0, Entrando = 1, ... }`. No PHP moderno, usamos `enum ParticipanteStatusEnum: int { case Ativo = 0; ... }`.
  - **Falso Amigo:** No TypeScript, enums se comportam como valores primitivos em tempo de execução. No PHP, cada case é um objeto/instância! Para salvar no banco ou comparar valor bruto, usa-se `$status->value`. Para desserializar do banco para o enum, usa-se `ParticipanteStatusEnum::from($val)` ou o cast do Eloquent.
- **Tabela Pivô N:N vs Arrays aninhados / DTOs:** No frontend/Angular, um objeto `project` costuma ter um array `members: User[]`. No banco relacional, relacionamentos Muitos-para-Muitos exigem uma tabela associativa intermediária com chaves estrangeiras de ambos os lados e atributos próprios (como status e datas de vínculo).
  - **Falso Amigo:** No Eloquent, acessar `$projeto->participantes` retorna uma coleção de modelos `Usuario`. Para acessar os dados que estão fisicamente na tabela intermediária (ex.: a coluna `flags`), você acessa a propriedade mágica `$usuario->pivot->flags`. Se esquecer de encadear `->withPivot('flags')` na definição da relação no Model, o Eloquent ignora a coluna customizada e ela vem como `null`!

## Contrato de comportamento (linguagem natural)
- **Caminho Feliz:**
  - A migration cria a tabela `projeto_usuario` com `id`, `projeto_id`, `usuario_id`, `flags` (smallint, default 0: Ativo) e `timestamps`.
  - O par `(projeto_id, usuario_id)` possui índice de unicidade composto (um usuário só tem um vínculo por projeto).
  - `$projeto->participantes()->attach($usuario->id, ['flags' => ParticipanteStatusEnum::Ativo->value])` vincula o usuário ao projeto.
  - `$projeto->participantes` lista os usuários participantes, expondo os dados da pivô via `$usuario->pivot->flags`.
  - `$usuario->projetosParticipados` lista os projetos nos quais o usuário participa.
  - Se um `Projeto` ou `Usuario` for deletado fisicamente em testes, as linhas pivô relacionadas são removidas por cascata (`cascadeOnDelete`).
- **Casos Adversos:**
  - Tentar associar o mesmo usuário duas vezes ao mesmo projeto gera violação de chave única (`IntegrityConstraintViolationException` / código SQL de chave duplicada).
  - Tentar associar `projeto_id` ou `usuario_id` que não existem no banco é rejeitado por restrição de chave estrangeira.
  - Tentar instanciar `ParticipanteStatusEnum::from(99)` com um inteiro não mapeado lança `ValueError`.

## Partes (decomposição atômica)

### Parte 1: Enum `ParticipanteStatusEnum`
- **Arquivos afetados:** `app/Enums/ParticipanteStatusEnum.php`
- **Conceito-chave:** Backed Enum inteiro (`: int`) com os cases: `Ativo = 0`, `Entrando = 1`, `Saindo = 2`, `Historico = 3` e método auxiliar `label(): string` para exibição na UI.
- **Como verificar:** Tinker ou teste unitário instanciando os cases e validando os valores e labels.
- **Pergunta de checagem:** Qual a diferença entre `ParticipanteStatusEnum::from(99)` e `ParticipanteStatusEnum::tryFrom(99)`?

### Parte 2: Migration da Tabela Pivô `projeto_usuario`
- **Arquivos afetados:** `database/migrations/xxxx_xx_xx_xxxxxx_create_projeto_usuario_table.php`
- **Conceito-chave:** Chaves estrangeiras (`constrained('projetos')->cascadeOnDelete()`, `constrained('usuarios')->cascadeOnDelete()`), coluna `smallInteger('flags')` com default `0`, `timestamps()` e índice único composto `$table->unique(['projeto_id', 'usuario_id'])`.
- **Como verificar:** Rodar `php artisan migrate` e inspecionar a estrutura da tabela no banco de dados.
- **Pergunta de checagem:** Por que usar `$table->unique(['projeto_id', 'usuario_id'])` em vez de apenas índices simples separados em cada coluna?

### Parte 3: Relacionamentos `belongsToMany` nos Models (`Projeto` e `Usuario`)
- **Arquivos afetados:** `app/Models/Projeto.php`, `app/Models/Usuario.php`
- **Conceito-chave:** Métodos `participantes(): BelongsToMany` em `Projeto` e `projetosParticipados(): BelongsToMany` em `Usuario` utilizando `withPivot('flags')` e `withTimestamps()`.
- **Como verificar:** Executar operações no `php artisan tinker` (`attach`, consulta via `$projeto->participantes`).
- **Pergunta de checagem:** O que acontece com o atributo `flags` se você não declarar `withPivot('flags')` no método de relacionamento do Eloquent?

### Parte 4: Testes Automatizados (PHPUnit)
- **Arquivos afetados:** `tests/Feature/ProjetoParticipantePivotTest.php`
- **Conceito-chave:** Testes de integração com `RefreshDatabase` cobrindo: vinculação com `attach`, recuperação de dados da pivô, integridade de exclusão em cascata, e bloqueio de duplicidade pelo índice único.
- **Como verificar:** `php artisan test --compact --filter=ProjetoParticipantePivotTest`.
- **Pergunta de checagem:** Como testamos no PHPUnit que uma exceção específica de integridade de banco de dados foi lançada?

## Fora de escopo
- A Domain Action `CreateProjetoAction` e transação atômica na criação de projeto (tarefa E4-T5).
- Interface visual e componentes de acordeão/tabs de participantes (tarefa E5-T1).
- Máquina de transição de status (indicar, aceitar, desligar) (tarefas do Épico 5).

## Fechamento (checklist de entendimento)
- [ ] **Explicar:** O papel de uma tabela pivô, índices de unicidade e Backed Enums do PHP 8.3+.
- [ ] **Tracear:** O caminho que o Eloquent percorre para carregar `$projeto->participantes` e acessar `$usuario->pivot->flags`.
- [ ] **Reconstruir:** Criar um relacionamento `belongsToMany` com atributos na pivô sem consulta prévia.

