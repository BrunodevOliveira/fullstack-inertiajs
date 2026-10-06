# E4-T3 - Formulário de Cadastro e Edição de Projetos (Dropdown encadeado Curso → Depto, validação de vagas)

## Objetivo de aprendizagem (o que vou saber explicar ao final)
- Estruturar o fluxo completo de criação e edição com Inertia.js (`create`, `store`, `edit`, `update`).
- Validar regras de domínio complexas e relacionamentos via `FormRequest` (`ProjetoRequest`).
- Proteger rotas de mutação (`store`, `update`) e exibição de formulários (`create`, `edit`) utilizando Policies do Laravel (`authorizeResource` ou `$this->authorize()` / Gate).
- Implementar reatividade encadeada (cascading dropdown Curso → Departamento) no Vue 3 usando Composition API (`computed`, `watch` e `useForm`).
- Garantir segurança contra IDOR (o docente responsável é sempre resolvido pelo usuário logado, nunca por input arbitrário do cliente).
- Escrever testes automatizados de Feature cobrindo autorização, validação de vagas, integridade referencial e persistência.

## Ponte Angular → Laravel/Vue (analogias e falsos amigos)
- **Rotas de Tela vs Rotas de Mutação (Inertia vs SPA Clássica):**
  - No Angular puro, a rota apenas carrega o componente `ProjectFormComponent`, que depois faz um `HttpClient.get()` para buscar opções e um `HttpClient.post()` para salvar.
  - No Inertia, o próprio controller do Laravel (`create` / `edit`) resolve os dados de suporte (`cursos`, `departamentos`, `agencias`) e os entrega prontos nas `props` do componente Vue no primeiro carregamento, eliminando "spinners" de inicialização e rotas intermediárias desnecessárias.
- **FormGroup vs `useForm`:**
  - No Angular: `new FormGroup({ titulo: new FormControl('', [Validators.required]) })`.
  - No Vue + Inertia: `const form = useForm({ titulo: '', curso_id: null, departamento_id: null, vagas: 1, ... })`. O `form.post()` e `form.put()` cuidam nativamente do envio, estados de loading (`form.processing`) e mapeamento automático de erros retornados pelo Laravel (`form.errors`).
- **Cascading Selects (Dropdown Encadeado):**
  - No Angular: escuta `cursoControl.valueChanges.subscribe(...)` ou `toSignal()` combinado com `computed()`.
  - No Vue 3: `const filteredDepartamentos = computed(() => props.departamentos.filter(d => d.curso_id === form.curso_id))`.
- **Falso Amigo (Persistência do Pai de Relacionamento):**
  - Na tela, o usuário seleciona **Curso** e depois **Departamento**.
  - No banco de dados, a tabela `projetos` possui **apenas** `departamento_id`, pois `departamentos` já guarda `curso_id`. O campo `curso_id` no formulário é puramente um estado de apoio de UI! Tentar salvar `curso_id` diretamente em `projetos` geraria erro de coluna inexistente ou redundância no schema.
- **Falso Amigo (IDOR no Responsável):**
  - Nunca confie em um `<input type="hidden" name="responsavel_id">` enviado pelo cliente. O `responsavel_id` deve ser atribuído no servidor pelo usuário autenticado (`$request->user()->id`), respeitando as regras da Policy.

## Contrato de comportamento (linguagem natural)

### Caminho Feliz:
1. **Criação (`create` e `store`):**
   - Docente autenticado com Lattes acessa `/projetos/criar`.
   - Página renderiza campos: Título, Assunto, Descrição, Vagas, Agência de Fomento (opcional), Dropdown Curso e Dropdown Departamento.
   - O dropdown de Departamento permanece desabilitado/vazio até que um Curso seja selecionado.
   - Ao selecionar um Curso, o dropdown de Departamento exibe apenas os departamentos daquele curso.
   - Ao enviar o formulário com dados válidos:
     - `vagas` deve ser um inteiro maior ou igual a 1.
     - `departamento_id` deve existir e pertencer a um departamento não deletado.
     - `responsavel_id` é automaticamente vinculado ao ID do usuário autenticado.
     - O projeto é salvo e o usuário é redirecionado com mensagem de sucesso.

2. **Edição (`edit` e `update`):**
   - Docente responsável (ou Root/Admin) acessa `/projetos/{projeto}/editar`.
   - Formulário é pré-preenchido com dados existentes do projeto.
   - O select de Curso é inicializado automaticamente com o `curso_id` do departamento vinculado ao projeto.
   - Ao alterar os dados e submeter:
     - O projeto é atualizado no banco.
     - O usuário é redirecionado com mensagem de sucesso.

### Casos Adversos:
- **Não autorizados:**
  - Usuário deslogado (guest): redirecionado para `/login` (HTTP 401).
  - Usuário logado sem perfil Docente ou sem Lattes tentando acessar `create` ou enviar `store`: bloqueado por HTTP 403 (Policy).
  - Usuário tentando editar projeto de outro docente: bloqueado por HTTP 403 (`ProjetoPolicy@update`).
- **Validação:**
  - Título, assunto, descrição ausentes ou ultrapassando limites de caracteres.
  - Vagas menor que 1 ou não numérica: erro de validação ("O número de vagas deve ser no mínimo 1").
  - Departamento não pertencente ao banco ou inválido: erro de validação.
  - Troca de curso sem selecionar novo departamento correspondente: validação rejeita.

---

## Partes (decomposição atômica)

### Parte 1: FormRequest (`ProjetoRequest`) e Regras de Validação
- **Arquivos afetados:** `app/Http/Requests/ProjetoRequest.php`
- **Conceito-chave:** Centralização de regras (`required`, `string`, `min`, `max`, `exists`, `integer`), mensagens customizadas em português e regra de autorização no FormRequest delegando para a Policy.
- **Como verificar:** Criar e testar o FormRequest via Artisan e inspeção de regras.
- **Pergunta de checagem:** Por que centralizar a validação em um `FormRequest` em vez de validar direto com `$request->validate()` no Controller?

### Parte 2: Autorização na `ProjetoPolicy` (`update`)
- **Arquivos afetados:** `app/Policies/ProjetoPolicy.php`
- **Conceito-chave:** Implementação do método `update(Usuario $usuario, Projeto $projeto)` garantindo que apenas o responsável original (ou Root/Admin via `before`) possa alterar o projeto.
- **Como verificar:** Testes de autorização verificando `can('update', $projeto)` para o dono vs outro docente.
- **Pergunta de checagem:** Como o método `before()` interage com a chamada do método `update()` quando um Admin tenta editar o projeto?

### Parte 3: Rotas Web e `ProjetoController`
- **Arquivos afetados:** `routes/web.php`, `app/Http/Controllers/ProjetoController.php`
- **Conceito-chave:** Ações RESTful com Inertia (`create`, `store`, `edit`, `update`), injeção de dependências, eager loading (`departamento.curso`) e vinculação segura de `$request->user()->id`.
- **Como verificar:** Rotas registradas em `php artisan route:list --name=projetos`.
- **Pergunta de checagem:** No método `edit()`, por que precisamos carregar o relacionamento `departamento.curso` antes de renderizar a página no Inertia?

### Parte 4: Interface Vue 3 com Dropdown Encadeado
- **Arquivos afetados:** `resources/js/pages/projetos/Create.vue`, `resources/js/pages/projetos/Edit.vue` (ou componente compartilhado `resources/js/pages/projetos/Form.vue`)
- **Conceito-chave:** PrimeVue v4 (`Select`, `InputText`, `Textarea`, `InputNumber`), `useForm` do Inertia, reatividade com `computed` para filtrar departamentos a partir do curso selecionado, e reset do departamento ao trocar de curso.
- **Como verificar:** Navegação no browser, teste de seleção de curso e conferência de filtragem da lista de departamentos.
- **Pergunta de checagem:** Se o usuário selecionou o Curso A e o Depto A1, e depois trocou para o Curso B, o que precisa acontecer com o campo `departamento_id` no formulário?

### Parte 5: Suíte de Testes Automatizados (PHPUnit)
- **Arquivos afetados:** `tests/Feature/ProjetoControllerTest.php`
- **Conceito-chave:** Feature tests cobrindo:
  - Exibição das páginas `create` e `edit` para usuários autorizados.
  - Bloqueio 403 para usuários não autorizados.
  - Criação com sucesso e conferência no banco (`assertDatabaseHas`).
  - Falha de validação para vagas <= 0.
  - Atualização por responsável vs bloqueio para outro docente.
- **Como verificar:** `php artisan test --compact --filter=ProjetoControllerTest`.
- **Pergunta de checagem:** O que a asserção `assertDatabaseHas('projetos', [...])` verifica no banco durante o teste?

---

## Fora de escopo
- Listagem geral e catálogo público de projetos (E4-T5 e E4-T6).
- Vínculo do docente como participante na tabela pivô (E4-T4).
- Regra de bloqueio de redução de vagas abaixo de alunos já ativos (E4-T7).
- Exclusão ou arquivamento de projetos (E4-T7).

---

## Fechamento (checklist de entendimento)
- [x] **Explicar:** Como funciona a integração entre FormRequest, Policy e Controller no fluxo de persistência do Laravel.
- [x] **Explicar:** Como a reatividade do Vue 3 (`computed` e `useForm`) resolve o dropdown encadeado sem chamadas adicionais de rede.
- [x] **Tracear:** O ciclo de vida de uma submissão inválida (desde o clique em salvar até a exibição dos erros sob os campos).
- [x] **Reconstruir:** Explicar a lógica de autorização do `update` sem consultar o código.

