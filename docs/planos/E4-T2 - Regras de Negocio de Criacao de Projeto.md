# E4-T2 - Regras de Negócio de Criação de Projeto (Policy docente + validação de link Lattes)

## Objetivo de aprendizagem (o que vou saber explicar ao final)
- Compreender a diferença prática e arquitetural entre Middleware e Policies no Laravel.
- Implementar e entender o funcionamento do mecanismo de autorização baseado em entidades do Laravel (`Policy`).
- Utilizar objetos de resposta de autorização ricos (`Illuminate\Auth\Access\Response`) para fornecer mensagens customizadas de erro (HTTP 403).
- Escrever testes automatizados de Feature/Policy cobrindo caminhos autorizados e rejeitados para perfis distintos e estados de dados (com e sem Lattes).

## Ponte Angular → Laravel/Vue (analogias e falsos amigos)
- **Middleware vs Policy (Ponte com Guards e Permission Services):** 
  - No Angular, um `CanActivate` na rota decide se a página pode ser carregada em alto nível (ex.: "usuário está logado?"). No Laravel, isso é o papel de um **Middleware**.
  - No Angular, quando você precisa de uma regra específica de entidade (ex.: "este usuário pode editar este formulário específico?"), você costuma criar um método em um serviço (`AuthService.canCreateProject()`). No Laravel, o lugar canônico para isso é a **Policy**.
- **Falso Amigo (Segurança de Front vs Servidor):**
  - No Angular, desabilitar um botão com `[disabled]="!isDocente"` ou esconder com `*ngIf` **não é segurança** — é apenas UX/conveniência. Qualquer usuário pode abrir o console e disparar um `fetch` ou cURL com payload malicioso.
  - No Laravel, a **Policy é a guardiã da verdade no servidor**. Mesmo que o frontend falhe ou seja contornado, a Policy intercepta a requisição e retorna `403 Forbidden`.

## Contrato de comportamento (linguagem natural)
- **Caminho Feliz:**
  - Usuário com perfil `Docente` que possui o campo `lattes` preenchido com link válido recebe autorização (`allow`) para criar projetos.
  - Usuários administradores (`Root` ou `Administrador`) recebem autorização via verificação administrativa (`before`).
- **Casos Adversos:**
  - Usuário com perfil `Docente`, mas com campo `lattes` nulo, vazio ou sem preenchimento válido: autorização **negada** (`deny`) com mensagem amigável instruindo o preenchimento do perfil.
  - Usuário autenticado que NÃO possui perfil `Docente` (ex.: `Discente` ou `Tecnico`): autorização **negada** (`deny`) com mensagem indicando que apenas docentes podem submeter projetos.
  - Usuário não autenticado (guest): bloqueado por autenticação (HTTP 401).

## Partes (decomposição atômica)

### Parte 1: Geração e Estrutura da `ProjetoPolicy`
- **Arquivos afetados:** `app/Policies/ProjetoPolicy.php`
- **Conceito-chave:** O que é uma Policy, descoberta automática no Laravel 11/12, convenção de nomes (`ModelPolicy`) e assinatura dos métodos de autorização.
- **Como verificar:** Comando `php artisan make:policy ProjetoPolicy --model=Projeto`.
- **Pergunta de checagem:** O que o parâmetro `--model=Projeto` faz na geração da Policy pelo Artisan?

### Parte 2: Implementação das Regras de Negócio (`before` e `create`)
- **Arquivos afetados:** `app/Policies/ProjetoPolicy.php`
- **Conceito-chave:** Uso do método especial `before()` para superusuários e retorno de `Response::allow()` vs `Response::deny(...)` com validação de perfil e presença do Lattes.
- **Como verificar:** Testes manuais via Tinker instanciando usuários e checando `Gate::forUser($user)->inspect('create', Projeto::class)`.
- **Pergunta de checagem:** Por que usar `Response::deny('Mensagem')` é superior a retornar simplesmente `false` dentro do método da Policy?

### Parte 3: Suíte de Testes de Autorização (PHPUnit)
- **Arquivos afetados:** `tests/Feature/ProjetoPolicyTest.php`
- **Conceito-chave:** Testes focados na camada de autorização cobrindo matriz de permissões: Docente com Lattes, Docente sem Lattes, Discente, Root e Guest.
- **Como verificar:** Executar `php artisan test --compact --filter=ProjetoPolicyTest`.
- **Pergunta de checagem:** Qual a diferença entre `$user->can('create', Projeto::class)` e `Gate::authorize('create', Projeto::class)`?

## Fora de escopo
- Telas Vue e formulário de cadastro de projetos (E4-T3).
- Validação dos dados do formulário como título, assunto, vagas (E4-T3).
- Vínculo automático de participantes no banco de dados (E4-T4).

## Fechamento (checklist de entendimento)
- [x] **Explicar:** A diferença entre Middleware e Policy e como o Laravel resolve a autorização automaticamente.
- [x] **Tracear:** O fluxo de uma requisição desde a chamada do Gate até a emissão da resposta `Response::deny`.
- [x] **Reconstruir:** Criar uma Policy para outro model com regra condicional sem auxílio de IA.
