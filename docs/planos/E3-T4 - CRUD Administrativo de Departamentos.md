# Plano de Implementação - E3-T4: CRUD Administrativo de Departamentos / Laboratórios

Implementar o gerenciamento administrativo completo de **Departamentos e Laboratórios**, permitindo que usuários com perfil **Super Administrador (Root)** ou **Administrador** listem, criem, editem e excluam departamentos/laboratórios acadêmicos vinculados aos seus respectivos cursos, com validação de unicidade composta por curso, filtros rápidos e proteção de integridade referencial.

---

## 🎯 Proposta Técnica

A tarefa será conduzida de forma estritamente incremental em 3 partes:

### Parte 1: Backend (FormRequest, Controller e Rotas)
- **FormRequest `app/Http/Requests/DepartamentoRequest.php`**:
  - `nome`: obrigatório, string, máximo de 100 caracteres.
  - Regra de unicidade composta por curso: o nome do departamento deve ser único dentro do mesmo curso, desconsiderando registros em soft delete (`withoutTrashed()`) e ignorando o próprio registro no caso de atualização:
    ```php
    Rule::unique('departamentos', 'nome')
        ->where(fn ($query) => $query->where('curso_id', $this->curso_id))
        ->ignore($departamentoId)
        ->withoutTrashed()
    ```
  - `curso_id`: obrigatório, inteiro, deve existir na tabela `cursos` entre registros ativos (`Rule::exists('cursos', 'id')->withoutTrashed()`).
  - Mensagens amigáveis em português para cada regra.
- **Controller `app/Http/Controllers/DepartamentoController.php`**:
  - Autorização estrita: apenas usuários autenticados com perfil `Root` ou `Administrador` (método `authorizedAdmin()`).
  - `index(Request $request)`:
    - Filtro de busca textual por nome do departamento (`where('nome', 'like', "%{$busca}%")`).
    - Filtro opcional por curso (`curso_id`).
    - Filtro opcional por campus (`campus_id`), filtrando via relacionamento com curso (`whereHas('curso', fn ($q) => $q->where('campus_id', $campusId))`).
    - Eager Loading: carregamento de `curso:id,nome,campus_id` e do campus aninhado `curso.campus:id,nome`.
    - Ordenação e paginação: `latest('id')->paginate(10)->withQueryString()`.
    - Dados auxiliares enviados para os dropdowns: lista de cursos ativos ordenados por nome com o campus correspondente (`Curso::with('campus:id,nome')->orderBy('nome')->get(['id', 'nome', 'campus_id'])`) e lista de campi (`Campus::orderBy('nome')->get(['id', 'nome'])`).
    - Renderiza `admin/departamentos/Index` via Inertia.
  - `store(DepartamentoRequest $request)`:
    - Criação do departamento com dados validados (`Departamento::create($request->validated())`).
    - Redirecionamento com mensagem flash de sucesso: `'Departamento/Laboratório cadastrado com sucesso!'`.
  - `update(DepartamentoRequest $request, Departamento $departamento)`:
    - Atualização do departamento com dados validados via Route Model Binding (`$departamento->update($request->validated())`).
    - Redirecionamento com mensagem flash de sucesso: `'Departamento/Laboratório atualizado com sucesso!'`.
  - `destroy(Request $request, Departamento $departamento)`:
    - Verificação de integridade referencial: preparação para o Épico 4 (caso a tabela `projetos` exista no futuro ou método `projetos()` esteja associado):
      - Se houver projetos vinculados, impede a exclusão com mensagem de erro amigável.
    - Se não houver impedimentos, executa `$departamento->delete()` (Soft Delete).
    - Redirecionamento com mensagem flash de sucesso: `'Departamento/Laboratório excluído com sucesso!'`.
- **Rotas em `routes/web.php`**:
  - Agrupadas sob `Route::middleware('auth')`:
    ```php
    Route::resource('departamentos', DepartamentoController::class)
        ->parameters(['departamentos' => 'departamento'])
        ->only(['index', 'store', 'update', 'destroy']);
    ```

### Parte 2: Frontend (Página Vue 3 com DataTable, Filtros e Dialogs)
- **Componente `resources/js/pages/admin/departamentos/Index.vue`**:
  - Utiliza `AppHeader` e identidade visual padronizada com `AppLayout`.
  - Barra superior de ações e filtros:
    - Campo de busca por nome do departamento com tecla Enter.
    - Dropdown `Select` do PrimeVue para filtrar por Campus.
    - Dropdown `Select` do PrimeVue para filtrar por Curso (com reset reativo ao alterar campus).
    - Botão *"Novo Departamento"*.
  - `DataTable` do PrimeVue:
    - Colunas: ID, Nome do Departamento / Laboratório, Curso Vinculado, Campus, Data de Cadastro e Ações (Editar e Excluir).
    - Paginação remota sincronizada com o backend (`@page="onPage"`).
  - Modal `Dialog` para Criação / Edição:
    - Gerenciado pelo `useForm` do Inertia (`nome`, `curso_id`).
    - Campo `nome` (InputText).
    - Campo `curso_id` (Select com cursos disponíveis e identificação do campus).
    - Tratamento reativo de erros de validação em cada campo.
  - Modal `Dialog` para Confirmação de Exclusão:
    - Confirmação segura antes de efetuar a exclusão via `router.delete()`.

### Parte 3: Integração no Menu e Verificação Prática
- Adição do link **Departamentos** no menu de navegação em `resources/js/layouts/AppLayout.vue` (`{ label: 'Departamentos', href: '/departamentos', icon: 'pi pi-sitemap' }`).
- Teste ponta a ponta:
  - Listagem inicial com os departamentos semeados pelo `EstruturaAcademicaSeeder`;
  - Filtros cruzados (busca por texto, filtro por campus e filtro por curso);
  - Cadastro de novo departamento com validação de unicidade composta;
  - Teste da validação composta: tentar cadastrar outro departamento com mesmo nome no mesmo curso (erro de validação) vs cadastrar departamento com mesmo nome em cursos diferentes (permitido);
  - Edição de departamento existente;
  - Exclusão bem-sucedida de departamento recém-criado;
  - Verificação de controle de acesso (perfil não-administrador recebendo HTTP 403).

---

## 📂 Arquivos Afetados

* **[NEW]** `docs/planos/E3-T4 - CRUD Administrativo de Departamentos.md`
* **[NEW]** `app/Http/Requests/DepartamentoRequest.php`
* **[NEW]** `app/Http/Controllers/DepartamentoController.php`
* **[NEW]** `resources/js/pages/admin/departamentos/Index.vue`
* **[MODIFY]** `routes/web.php`
* **[MODIFY]** `resources/js/layouts/AppLayout.vue`
* **[MODIFY]** `docs/epics-e-tarefas.md`

---

## 🧪 Verificação Manual

1. Logar como **Super Administrador (Root)** ou **Administrador** e navegar até `/departamentos`.
2. Verificar a listagem inicial dos departamentos previamente criados no seeder.
3. Testar os filtros por nome, campus e curso.
4. Clicar em *"Novo Departamento"*, preencher nome e curso, e salvar.
5. Tentar salvar outro departamento com o mesmo nome para o mesmo curso e confirmar que a validação de duplicidade bloqueia.
6. Editar o departamento criado e alterar o nome.
7. Excluir o departamento de teste e confirmar o Toast de sucesso.
8. Trocar para o perfil **Docente** via Dev Switcher e tentar acessar `/departamentos` para validar o bloqueio 403.
