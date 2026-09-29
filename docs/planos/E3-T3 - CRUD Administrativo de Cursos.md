# Plano de Implementação - E3-T3: CRUD Administrativo de Cursos

Implementar o gerenciamento administrativo completo de **Cursos**, permitindo que usuários com perfil **Super Administrador (Root)** ou **Administrador** listem, criem, editem e excluam cursos acadêmicos vinculados a seus respectivos campi, com seleção de disciplina de referência (limite de períodos PINC), e-mail institucional de contato, flag de colaborador externo e proteção estrita contra exclusão quando houver departamentos/laboratórios associados.

---

## 🎯 Proposta Técnica

A tarefa será conduzida de forma estritamente incremental em 3 partes:

### Parte 1: Backend (FormRequest, Controller e Rotas)
- **FormRequest `app/Http/Requests/CursoRequest.php`**:
  - `nome`: obrigatório, string, máximo de 100 caracteres, único por campus entre registros ativos (`Rule::unique('cursos', 'nome')->where('campus_id', $this->campus_id)->ignore($cursoId)->withoutTrashed()`).
  - `campus_id`: obrigatório, inteiro, deve existir na tabela `campuses` entre registros ativos (`Rule::exists('campuses', 'id')->withoutTrashed()`).
  - `disciplina_id`: opcional (nullable), inteiro, deve existir na tabela `disciplinas` (`Rule::exists('disciplinas', 'id')`).
  - `email`: opcional (nullable), e-mail válido, string, máximo de 255 caracteres.
  - `colaborador`: booleano (true/false).
  - Mensagens amigáveis em português para cada regra.
- **Controller `app/Http/Controllers/CursoController.php`**:
  - Autorização estrita: apenas usuários autenticados com perfil `Root` ou `Administrador` (mesmo padrão do `CampusController`).
  - `index(Request $request)`:
    - Filtro de busca textual por nome do curso (`where('nome', 'like', "%{$busca}%")`).
    - Filtro opcional por campus (`campus_id`).
    - Carregamento de relacionamentos: `campus:id,nome`, `disciplina:id,nome`.
    - Contagem otimizada de departamentos vinculados: `withCount('departamentos')`.
    - Ordenação e paginação: `latest('id')->paginate(10)->withQueryString()`.
    - Envio dos dados auxiliares para os dropdowns: lista de todos os campi ativos (`Campus::orderBy('nome')->get(['id', 'nome'])`) e lista de disciplinas (`Disciplina::orderBy('id')->get(['id', 'nome'])`).
    - Renderiza `admin/cursos/Index` via Inertia.
  - `store(CursoRequest $request)`:
    - Criação do curso com dados validados (`Curso::create($request->validated())`).
    - Redirecionamento para a listagem com flash message de sucesso (`'Curso cadastrado com sucesso!'`).
  - `update(CursoRequest $request, Curso $curso)`:
    - Atualização do curso com dados validados via Route Model Binding (`$curso->update($request->validated())`).
    - Redirecionamento para a listagem com flash message de sucesso (`'Curso atualizado com sucesso!'`).
  - `destroy(Request $request, Curso $curso)`:
    - Verificação de integridade referencial: se `$curso->departamentos()->exists()`, impede a exclusão e retorna erro via flash message: *"Não é possível excluir este curso pois existem departamentos/laboratórios vinculados a ele."*.
    - Se não houver dependências, executa `$curso->delete()` (Soft Delete) e retorna sucesso (`'Curso excluído com sucesso!'`).
- **Rotas em `routes/web.php`**:
  - Agrupadas sob `Route::middleware('auth')`:
    ```php
    Route::resource('cursos', CursoController::class)
        ->parameters(['cursos' => 'curso'])
        ->only(['index', 'store', 'update', 'destroy']);
    ```

### Parte 2: Frontend (Página Vue 3 com DataTable, Filtros e Dialogs)
- **Componente `resources/js/pages/admin/cursos/Index.vue`**:
  - Utiliza `AppHeader` e visual unificado com `AppLayout`.
  - Barra superior de ações:
    - Campo de busca por nome do curso com tecla Enter.
    - Dropdown `Select` do PrimeVue para filtrar por Campus (com opção de limpar filtro).
    - Botão *"Novo Curso"*.
  - `DataTable` do PrimeVue:
    - Colunas: ID, Nome do Curso, Campus (`Tag` ou badge), Disciplina de Referência (`disciplina?.nome` ou traço), E-mail institucional (link `mailto:` ou traço), Colaborador (`Tag` verde "Sim" / cinza "Não"), Departamentos vinculados (`Tag` com contagem), e Ações.
    - Paginação remota sincronizada com o backend (`@page="onPage"`).
  - Modal `Dialog` para Criação / Edição de Curso:
    - Gerenciado pelo `useForm` do Inertia (`nome`, `campus_id`, `disciplina_id`, `email`, `colaborador`).
    - Campo `nome` (InputText).
    - Campo `campus_id` (PrimeVue `Select` com lista de campi).
    - Campo `disciplina_id` (PrimeVue `Select` com lista de disciplinas PINC 1 a 4, limpável).
    - Campo `email` (InputText tipo email para contato institucional).
    - Campo `colaborador` (PrimeVue `ToggleSwitch` com rótulo descritivo explicando a flag).
    - Tratamento reativo de erros de validação em cada campo.
  - Modal `Dialog` para Confirmação de Exclusão:
    - Alerta visual caso o curso possua departamentos associados (`departamentos_count > 0`), com botão de exclusão desabilitado na tabela e confirmação segura.

### Parte 3: Integração no Menu e Verificação Prática
- Adição do link **Cursos** no menu de navegação em `resources/js/layouts/AppLayout.vue` (`{ label: 'Cursos', href: '/cursos', icon: 'pi pi-book' }`).
- Teste ponta a ponta:
  - Listagem e filtros cruzados (busca por texto + filtro por campus);
  - Cadastro de novo curso com e-mail institucional e flag colaborador;
  - Edição de curso existente;
  - Tentativa de exclusão de curso que possui departamentos (ex: "Ciências Biológicas" ou "Medicina" - bloqueado com mensagem de integridade);
  - Exclusão bem-sucedida de curso recém-criado sem departamentos;
  - Verificação de controle de acesso (perfil não-administrador recebendo HTTP 403).

---

## 📂 Arquivos Afetados

* **[NEW]** `app/Http/Requests/CursoRequest.php`
* **[NEW]** `app/Http/Controllers/CursoController.php`
* **[NEW]** `resources/js/pages/admin/cursos/Index.vue`
* **[MODIFY]** `routes/web.php`
* **[MODIFY]** `resources/js/layouts/AppLayout.vue`
* **[MODIFY]** `docs/epics-e-tarefas.md`

---

## 🧪 Verificação Manual

1. Logar como **Super Administrador (Root)** ou **Administrador** e navegar até `/cursos`.
2. Verificar a listagem inicial com os cursos previamente semeados pelo `EstruturaAcademicaSeeder`.
3. Filtrar pelo campus "Cidade Universitária (Fundão)" e conferir apenas os cursos daquele campus.
4. Clicar em *"Novo Curso"*, preencher os dados (ex: "Engenharia de Software", campus "Cidade Universitária (Fundão)", e-mail "contato@es.ufrj.br", Colaborador ativo) e salvar.
5. Editar o curso criado alterando o e-mail ou nome.
6. Tentar excluir o curso "Medicina" (que possui departamentos associados) e verificar se o sistema bloqueia exibindo a mensagem de integridade.
7. Excluir o curso "Engenharia de Software" recém-criado (sem departamentos) e verificar remoção e Toast de sucesso.
8. Trocar para perfil **Docente** ou **Discente** via Dev Switcher e tentar acessar `/cursos` para validar a trava de autorização (HTTP 403).

