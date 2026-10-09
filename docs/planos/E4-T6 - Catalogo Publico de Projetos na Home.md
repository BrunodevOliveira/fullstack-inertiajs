# E4-T6: Catálogo Público de Projetos na Home

## Objetivo de aprendizagem (o que vou saber explicar ao final)
- Como estruturar consultas com busca textual e paginação no Eloquent usando Local Scopes dinâmicos (`scopeBuscar`, `scopeAtivos`).
- Como prevenir o problema de **N+1 queries** em listagens públicas através de Eager Loading (`with(['responsavel', 'departamento'])`).
- Como gerenciar filtros de busca e paginação no frontend com Inertia.js usando `router.get`, `preserveState: true`, `preserveScroll: true` e `replace: true`.
- A diferença de indexação entre o paginador do Laravel (1-indexed) e o Paginator do PrimeVue (0-indexed).
- Como testar listagens públicas com filtros e paginação no PHPUnit usando `assertInertia`.

---

## Ponte Angular → Laravel/Vue (analogias e falsos amigos)
- **Analogia:** No Angular, criaríamos um `ProjetoService.getProjetos({ busca, page })` retornando um `Observable` via `HttpClient`, atualizando um Signal ou BehaviorSubject. No Inertia, não há camada manual de HTTP/RxJS: uma chamada `router.get('/', { busca, page }, { preserveState: true })` faz a requisição parcial em segundo plano e o Inertia atualiza reativamente as `props` do componente Vue.
- **Falso Amigo 1 (Perda de Estado sem `preserveState`):** No Angular, você altera query params no `ActivatedRoute` sem recriar o componente. No Inertia, se você não passar `{ preserveState: true }`, o Inertia assume uma navegação completa, recriando o componente do zero e fazendo com que o input de busca perca o foco e limpe variáveis locais.
- **Falso Amigo 2 (Índice de Página):** O componente `Paginator` do PrimeVue emite `event.page` começando em `0` (0-indexed). O `LengthAwarePaginator` do Laravel espera `page` iniciando em `1` (1-indexed). Enviar `event.page` direto causará dessincronia de uma página!
- **Falso Amigo 3 (`replace: true` vs Histórico):** A cada caractere digitado ou filtro acionado, se não usarmos `replace: true`, o navegador criará uma nova entrada no histórico, obrigando o usuário a clicar dezenas de vezes no botão "Voltar" para sair da página.

---

## Contrato de comportamento (linguagem natural)
- **Caminho Feliz:**
  - Visitante acessa `/`: visualiza projetos ativos (`arquivado = false`), ordenados pelos mais recentes, paginados (ex: 6 por página em grid responsivo).
  - Cada card exibe título, assunto, descrição resumida, vagas, nome do responsável e departamento.
  - Visitante digita um termo no campo de busca: requisição vai para `/?busca=termo`, filtrando projetos que contenham o termo no título, assunto ou descrição. A URL atualiza sem poluir o histórico (`replace: true`).
  - Visitante clica na página 2: a busca é mantida (`?busca=termo&page=2`) e o scroll é preservado (`preserveScroll: true`).
  - Eager Loading ativo: as relações `responsavel` e `departamento` são trazidas em queries pré-agrupadas (máximo de 3 queries no banco para qualquer quantidade de itens na página).
- **Casos Adversos:**
  - Projetos arquivados (`arquivado = true`) ou excluídos via Soft Delete **nunca** devem ser exibidos no catálogo público.
  - Busca sem resultados: exibe um Empty State amigável (ícone, mensagem clara e botão para limpar filtros) em vez de uma tela quebrada ou tabela vazia.
  - Usuário digita espaços em branco ou busca vazia: o backend ignora o filtro e a URL limpa o parâmetro `busca` (`undefined` no payload do Inertia).

---

## Partes (decomposição atômica)

### Parte 1: Escopo de Busca Textual e Eager Loading no Model `Projeto`
- **Arquivos afetados:** `app/Models/Projeto.php`
- **Conceito-chave:** Criação do `scopeBuscar(Builder $query, ?string $termo): void` usando agrupamento lógico de `where` (`title`, `assunto`, `descricao`) com operador `LIKE` seguro contra SQL Injection, combinando com o já existente `scopeAtivos()`.
- **Como verificar:** Tinker executando `Projeto::ativos()->buscar('termo')->toSql()`.
- **Pergunta de checagem:** Por que condições `OR` dentro de um escopo de busca devem ser envelopadas em uma função anônima `$query->where(function ($q) { ... })`?

### Parte 2: Orquestração no `HomeController@index`
- **Arquivos afetados:** `app/Http/Controllers/HomeController.php`
- **Conceito-chave:** Captura do parâmetro `busca` da `Request`, execução da query no Model com `with(['responsavel', 'departamento'])`, ordenação decrescente (`latest()`), paginação com `paginate(6)->withQueryString()` e envio das props (`projetos`, `filters`) para o Inertia.
- **Como verificar:** Acessar `http://localhost:8000/?busca=teste` no navegador e inspecionar os props recebidos na aba Inertia do Vue DevTools ou inspecionando o HTML inicial.
- **Pergunta de checagem:** Para que serve o método `->withQueryString()` encadeado no paginador do Laravel?

### Parte 3: Interface no `Home.vue` (Grid de Cards, Busca e Paginação)
- **Arquivos afetados:** `resources/js/pages/Home.vue`
- **Conceito-chave:** Estruturação de grid responsivo com cards (PrimeVue `Card`), badges de status/vagas, campo de busca com debounce/enter (`InputText` + `IconField`), Paginator remoto e Empty State. Navegação reativa com `router.get` usando `preserveState`, `preserveScroll` e `replace`.
- **Como verificar:** Testar no navegador a digitação no filtro, navegação de páginas, botão de limpar filtros e redimensionamento responsivo.
- **Pergunta de checagem:** O que acontece com os dados paginados e a URL se você filtrar algo estando na página 3? Por que devemos resetar para a página 1 ao aplicar uma nova busca?

### Parte 4: Teste de Feature Automatizado
- **Arquivos afetados:** `tests/Feature/HomeControllerTest.php`
- **Conceito-chave:** Criação de testes cobrindo: exibição de projetos ativos com `assertInertia`, isolamento (projetos arquivados não aparecem), busca textual assertiva e manutenção de query params na paginação.
- **Como verificar:** `php artisan test --compact --filter=HomeControllerTest`.
- **Pergunta de checagem:** Como o método `assertInertia` do Laravel valida os props passados para o Vue sem precisar renderizar o JavaScript?

---

## Fora de escopo
- Listagem autenticada restrita ("Meus Projetos" vs "Todos os Projetos") e ações de edição/exclusão (escopo da tarefa **E4-T7**).
- Inscrição, solicitação de vagas ou indicação de discentes (escopo do **Épico 5**).
- Modais de detalhes aprofundados de avaliação ou relatórios (escopo do **Épico 6**).

---

## Fechamento (checklist de entendimento)
- [ ] **Explicar:** Por que a Home pública usa `HomeController` enquanto o CRUD autenticado usa `ProjetoController`.
- [ ] **Tracear:** Rastreamento do ciclo completo: input do usuário no Vue ➔ `router.get` com `preserveState` ➔ `HomeController` ➔ `scopeBuscar` + `withQueryString` ➔ hidratação no Vue.
- [ ] **Reconstruir:** Capacidade de criar uma listagem com busca e paginação com Inertia sem consultar o plano.

