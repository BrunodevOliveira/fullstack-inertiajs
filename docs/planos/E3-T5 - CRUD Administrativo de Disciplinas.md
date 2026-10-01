# Plano de Implementação - E3-T5: CRUD Administrativo de Disciplinas / Períodos (PINC)

Implementar o gerenciamento administrativo completo de **Disciplinas / Períodos do Programa (PINC)**, permitindo que usuários com perfil **Super Administrador (Root)** ou **Administrador** listem, criem, editem e excluam os períodos institucionais (ex: PINC 1, PINC 2, PINC 3, etc.), com ordenação natural, validação de unicidade com limite de 10 caracteres e proteção estrita de integridade referencial com bloqueio de exclusão quando associada a cursos ou avaliações.

---

## 🎯 Proposta Técnica

A tarefa será conduzida de forma estritamente incremental em 3 partes:

### Parte 1: Backend (FormRequest, Controller e Rotas)
- **FormRequest `app/Http/Requests/DisciplinaRequest.php`**:
  - `nome`: obrigatório, string, máximo de 10 caracteres (conforme schema da tabela `disciplinas`).
  - Unicidade na tabela `disciplinas` ignorando o próprio ID em atualizações e desconsiderando registros excluídos (`withoutTrashed()`):
    ```php
    Rule::unique('disciplinas', 'nome')
        ->ignore($disciplinaId)
        ->withoutTrashed()
    ```
  - Mensagens amigáveis em português para cada regra.
- **Controller `app/Http/Controllers/DisciplinaController.php`**:
  - Autorização estrita: apenas usuários autenticados com perfil `Root` ou `Administrador` (método `authorizedAdmin()`).
  - `index(Request $request)`:
    - Filtro de busca textual por nome da disciplina (`where('nome', 'like', "%{$busca}%")`).
    - Contagem de cursos vinculados (`withCount('cursos')`).
    - Ordenação natural consistente: `orderByRaw('LENGTH(nome) ASC, nome ASC')`.
    - Paginação: `paginate(10)->withQueryString()`.
    - Renderiza `admin/disciplinas/Index` via Inertia com dados e filtros.
  - `store(DisciplinaRequest $request)`:
    - Criação da disciplina com dados validados (`Disciplina::create($request->validated())`).
    - Redirecionamento com mensagem flash de sucesso: `'Disciplina/Período cadastrado com sucesso!'`.
  - `update(DisciplinaRequest $request, Disciplina $disciplina)`:
    - Atualização via Route Model Binding (`$disciplina->update($request->validated())`).
    - Redirecionamento com mensagem flash de sucesso: `'Disciplina/Período atualizado com sucesso!'`.
  - `destroy(Request $request, Disciplina $disciplina)`:
    - Bloqueio de exclusão caso haja avaliações associadas (requisito do PINC e preparação para o Épico 6):
      ```php
      if (Schema::hasTable('avaliacoes') && method_exists($disciplina, 'avaliacoes') && $disciplina->avaliacoes()->exists()) {
          return redirect()->route('disciplinas.index')
              ->with('error', 'Não é possível excluir esta disciplina/período pois existem avaliações vinculadas a ela.');
      }
      ```
    - Bloqueio de exclusão caso haja cursos vinculados como disciplina de referência:
      ```php
      if ($disciplina->cursos()->exists()) {
          return redirect()->route('disciplinas.index')
              ->with('error', 'Não é possível excluir esta disciplina/período pois existem cursos vinculados a ela.');
      }
      ```
    - Exclusão suave (`$disciplina->delete()`).
    - Redirecionamento com mensagem flash de sucesso: `'Disciplina/Período excluído com sucesso!'`.
- **Rotas em `routes/web.php`**:
  - Inserção das rotas resource sob `Route::middleware('auth')`:
    ```php
    Route::resource('disciplinas', DisciplinaController::class)
        ->parameters(['disciplinas' => 'disciplina'])
        ->only(['index', 'store', 'update', 'destroy']);
    ```

### Parte 2: Frontend (Página Vue 3 com DataTable, Dialogs e Validações)
- **Componente `resources/js/pages/admin/disciplinas/Index.vue`**:
  - Utiliza `AppHeader` com título e metatags.
  - Cabeçalho com título, descrição contextual e botão *"Nova Disciplina"*.
  - `Card` com busca textual reativa por Enter e botão de limpar busca.
  - `DataTable` do PrimeVue:
    - Colunas: ID (`#`), Nome da Disciplina/Período, Cursos Vinculados (Tag), e Ações (Editar e Excluir).
    - Desativação do botão de exclusão caso existam cursos vinculados (`cursos_count > 0`) com tooltip explicativo.
    - Paginação remota sincronizada (`@page="onPage"`).
  - Modal `Dialog` de Criação / Edição:
    - Formulário reativo com `useForm` (`nome`).
    - Validação de erros inline com componente `Message` do PrimeVue.
    - Botões de Cancelar e Submeter com estado de carregamento (`loading`).
  - Modal `Dialog` de Confirmação de Exclusão:
    - Diálogo seguro com ícone de alerta e confirmação via `router.delete()`.

### Parte 3: Integração no Menu e Verificação Prática
- Adição do link **Disciplinas (PINC)** no menu de navegação em `resources/js/layouts/AppLayout.vue` (`{ label: 'Disciplinas (PINC)', href: '/disciplinas', icon: 'pi pi-calendar' }`).
- Teste ponta a ponta:
  - Listagem inicial com os períodos semeados pelo `DisciplinaSeeder` (`PINC 1` a `PINC 4`);
  - Busca textual por nome;
  - Cadastro de nova disciplina/período (ex: `PINC 5`);
  - Teste de validação de unicidade: tentar cadastrar novamente `PINC 5`;
  - Teste de limite de caracteres (máx. 10);
  - Edição de disciplina;
  - Trava de exclusão: confirmar que o botão de excluir está desabilitado para disciplinas com cursos vinculados;
  - Exclusão bem-sucedida de disciplina recém-criada (sem cursos vinculados);
  - Verificação de controle de acesso (perfil não-administrador recebendo HTTP 403).

---

## 📂 Arquivos Afetados

* **[NEW]** `docs/planos/E3-T5 - CRUD Administrativo de Disciplinas.md`
* **[NEW]** `app/Http/Requests/DisciplinaRequest.php`
* **[NEW]** `app/Http/Controllers/DisciplinaController.php`
* **[NEW]** `resources/js/pages/admin/disciplinas/Index.vue`
* **[MODIFY]** `routes/web.php`
* **[MODIFY]** `resources/js/layouts/AppLayout.vue`
* **[MODIFY]** `docs/epics-e-tarefas.md`

---

## 🧪 Verificação Manual

1. Logar como **Super Administrador (Root)** ou **Administrador** e navegar até `/disciplinas`.
2. Verificar a listagem inicial com os 4 períodos (`PINC 1` a `PINC 4`) ordenados.
3. Testar a busca rápida por nome.
4. Clicar em *"Nova Disciplina"*, preencher `PINC 5` e cadastrar.
5. Tentar cadastrar `PINC 5` novamente e verificar a mensagem de validação de unicidade.
6. Editar o registro criado alterando para `PINC 5A` e salvar.
7. Testar a trava de exclusão em `PINC 1` (caso tenha cursos vinculados) e excluir o registro recém-criado `PINC 5A`.
8. Trocar para perfil **Docente** via Dev Switcher e tentar acessar `/disciplinas` para comprovar o bloqueio 403.
