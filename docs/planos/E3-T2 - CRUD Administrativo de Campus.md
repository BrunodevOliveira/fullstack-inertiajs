# Plano de Implementação - E3-T2: CRUD Administrativo de Campus

Implementar o gerenciamento administrativo completo de **Campi**, permitindo que usuários com perfil **Super Administrador (Root)** ou **Administrador** listem, criem, editem e excluam campi, com regras estritas de integridade que bloqueiam a exclusão caso existam cursos vinculados.

---

## 🎯 Proposta Técnica

A tarefa será conduzida de forma estritamente incremental em 3 partes:

### Parte 1: Backend (FormRequest, Controller e Rotas)
- **FormRequest `app/Http/Requests/CampusRequest.php`**:
  - Validação do campo `nome`: obrigatório, string, max 100 caracteres, único entre registros ativos (`Rule::unique('campuses', 'nome')->ignore($campus?->id)->withoutTrashed()`).
  - Mensagens amigáveis em português.
- **Controller `app/Http/Controllers/CampusController.php`**:
  - Autorização estrita: apenas usuários com perfil `Root` ou `Administrador`.
  - `index(Request $request)`:
    - Busca textual por nome do campus.
    - Contagem otimizada de cursos vinculados (`withCount('cursos')`).
    - Paginação via Eloquent (`paginate(10)->withQueryString()`).
    - Renderiza `admin/campuses/Index` via Inertia.
  - `store(CampusRequest $request)`:
    - Criação do campus e redirecionamento com flash message de sucesso.
  - `update(CampusRequest $request, Campus $campus)`:
    - Atualização do nome e retorno com flash message de sucesso.
  - `destroy(Campus $campus)`:
    - Verificação de dependências: se `$campus->cursos()->exists()`, impede a exclusão e redireciona de volta com flash de erro: *"Não é possível excluir este campus pois existem cursos vinculados a ele."*.
    - Se não houver dependências, executa `$campus->delete()` (Soft Delete) e retorna com mensagem de sucesso.
- **Rotas em `routes/web.php`**:
  - Agrupadas sob `middleware('auth')`:
    - `GET /campuses` (`campuses.index`)
    - `POST /campuses` (`campuses.store`)
    - `PUT /campuses/{campus}` (`campuses.update`)
    - `DELETE /campuses/{campus}` (`campuses.destroy`)

### Parte 2: Frontend (Página Vue 3 com DataTable e Dialog)
- **Componente `resources/js/pages/admin/campuses/Index.vue`**:
  - Utiliza `AppLayout`.
  - Barra de ações superior: Campo de busca rápida com debounce e botão *"Novo Campus"*.
  - `DataTable` do PrimeVue:
    - Colunas: ID, Nome do Campus, Quantidade de Cursos (`cursos_count` exibido com `Tag`/Badge visual), e Coluna de Ações.
    - Paginação remota integrada aos links do Laravel.
  - Modal `Dialog` reativo para Criação e Edição:
    - Formulário gerenciado pelo `useForm` do Inertia (`nome`).
    - Exibição de erros de validação em tempo real (`Message` ou inline).
    - Botão de envio com estado de carregamento (`processing`).
  - Modal/Confirmação de Exclusão:
    - Modal de confirmação seguro.
    - Bloqueio visual ou aviso enfático caso o campus possua cursos vinculados (`cursos_count > 0`), impedindo a ação antes mesmo do envio da requisição.

### Parte 3: Integração no Menu e Verificação Prática
- Adição do link **Campi** no menu lateral de navegação em `resources/js/layouts/AppLayout.vue` (com ícone `pi pi-building`, condicionado ou agrupado na seção administrativa).
- Teste ponta a ponta no navegador:
  - Listagem e paginação;
  - Criação de novo campus e validação de nome duplicado;
  - Edição de nome;
  - Tentativa de exclusão de campus com cursos (ex: Fundão - deve ser bloqueado);
  - Exclusão bem-sucedida de campus sem cursos.

---

## 📂 Arquivos Afetados

* **[NEW]** `app/Http/Requests/CampusRequest.php`
* **[NEW]** `app/Http/Controllers/CampusController.php`
* **[NEW]** `resources/js/pages/admin/campuses/Index.vue`
* **[MODIFY]** `routes/web.php`
* **[MODIFY]** `resources/js/layouts/AppLayout.vue`
* **[MODIFY]** `docs/epics-e-tarefas.md`

---

## 🧪 Verificação Manual

1. Logar como **Root** ou **Administrador** e acessar a rota `/campuses`.
2. Testar busca por nome (ex: "Fundão", "Macaé").
3. Clicar em *"Novo Campus"*, tentar enviar vazio para ver a validação, depois cadastrar um novo campus (ex: "Campus Resende").
4. Clicar em *"Editar"* no campus criado e alterar seu nome para "Campus Resende - Polo Avançado".
5. Excluir o campus recém-criado (sem cursos) e verificar a remoção e o Toast de sucesso.
6. Tentar excluir o campus "Cidade Universitária (Fundão)" (que possui cursos) e validar que a aplicação bloqueia a operação informando a restrição de dependências.
7. Acessar `/campuses` com perfil Discente ou Docente e confirmar o bloqueio de permissão (HTTP 403).

