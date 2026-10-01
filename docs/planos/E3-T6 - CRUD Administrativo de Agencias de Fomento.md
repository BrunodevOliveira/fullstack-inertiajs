# Plano de Implementação - E3-T6: CRUD Administrativo de Agências de Fomento

Implementar o gerenciamento administrativo completo de **Agências de Fomento**, permitindo que usuários com perfil **Super Administrador (Root)** ou **Administrador** listem, criem, editem e excluam as agências financeiras e institucionais (ex: CNPq, FAPERJ, CAPES, UFRJ, etc.), com filtros avançados por texto e tipo (Bolsista, Projeto, Ambos), validação de unicidade de sigla e nome, e proteção estrita do registro especial **"Sem Bolsa"** contra exclusão e descaracterização.

---

## 🎯 Proposta Técnica

A tarefa será conduzida de forma estritamente incremental em 3 partes:

### Parte 1: Backend (Model, FormRequest, Controller e Rotas)
- **Model `app/Models/Agencia.php`**:
  - Expor atributo computado `is_sem_bolsa` no `$appends` para que o frontend identifique de forma confiável o registro especial do sistema.
- **FormRequest `app/Http/Requests/AgenciaRequest.php`**:
  - `sigla`: obrigatória, string, máximo 20 caracteres, única na tabela `agencias` ignorando o próprio ID em atualizações e desconsiderando registros excluídos (`withoutTrashed()`).
  - `nome`: obrigatório, string, máximo 255 caracteres, único na tabela `agencias` ignorando o próprio ID em atualizações e desconsiderando registros excluídos (`withoutTrashed()`).
  - `tipo`: obrigatório, regra de validação via Enum `Rule::enum(AgenciaTipoEnum::class)`.
  - Proteção: caso seja uma atualização da agência "Sem Bolsa", impedir a alteração de sua sigla para preservar sua identidade no sistema.
  - Mensagens amigáveis em português para cada regra.
- **Controller `app/Http/Controllers/AgenciaController.php`**:
  - Autorização estrita: apenas usuários autenticados com perfil `Root` ou `Administrador` (método `authorizedAdmin()`).
  - `index(Request $request)`:
    - Filtro de busca textual por `sigla` ou `nome`.
    - Filtro por `tipo` de agência (`AgenciaTipoEnum`).
    - Ordenação alfabética consistente por `sigla ASC`.
    - Paginação: `paginate(10)->withQueryString()`.
    - Fornecer à view as opções do enum `AgenciaTipoEnum::options()` para selects/dropdowns.
    - Renderiza `admin/agencias/Index` via Inertia.
  - `store(AgenciaRequest $request)`:
    - Criação da agência com dados validados (`Agencia::create($request->validated())`).
    - Redirecionamento com mensagem flash de sucesso: `'Agência de fomento cadastrada com sucesso!'`.
  - `update(AgenciaRequest $request, Agencia $agencia)`:
    - Se for a agência "Sem Bolsa", manter sua sigla original.
    - Atualização via Route Model Binding (`$agencia->update($request->validated())`).
    - Redirecionamento com mensagem flash de sucesso: `'Agência de fomento atualizada com sucesso!'`.
  - `destroy(Request $request, Agencia $agencia)`:
    - Bloqueio estrito de exclusão do registro especial "Sem Bolsa" (`$agencia->isSemBolsa()`).
    - Bloqueio preventivo se houver projetos vinculados (preparação para o Épico 4).
    - Bloqueio preventivo se houver avaliações vinculadas (preparação para o Épico 6).
    - Exclusão suave (`$agencia->delete()`).
    - Redirecionamento com mensagem flash de sucesso: `'Agência de fomento excluída com sucesso!'`.
- **Rotas em `routes/web.php`**:
  - Inserção das rotas resource sob o grupo `Route::middleware('auth')`:
    ```php
    Route::resource('agencias', AgenciaController::class)
        ->parameters(['agencias' => 'agencia'])
        ->only(['index', 'store', 'update', 'destroy']);
    ```

### Parte 2: Frontend (Página Vue 3 com DataTable, Dialogs, Filtros e Proteção de "Sem Bolsa")
- **Componente `resources/js/pages/admin/agencias/Index.vue`**:
  - Utiliza `AppHeader` com título e metatags.
  - Cabeçalho com título, descrição contextual e botão *"Nova Agência"*.
  - `Card` de filtros reativos: busca textual por sigla/nome, dropdown por tipo (`Bolsista`, `Projeto`, `Ambos`) e botão de limpar filtros.
  - `DataTable` do PrimeVue:
    - Colunas: ID (`#`), Sigla, Nome da Agência, Tipo (com `Tag` colorida baseada no tipo), e Ações (Editar e Excluir).
    - Desativação do botão de exclusão para o registro "Sem Bolsa" com tooltip explicativo: *"Registro especial do sistema. Não pode ser excluído."*
    - Paginação remota sincronizada (`@page="onPage"`).
  - Modal `Dialog` de Criação / Edição:
    - Formulário reativo com `useForm` (`sigla`, `nome`, `tipo`).
    - Select/Dropdown do PrimeVue v4 com as opções de `tipo`.
    - Bloqueio do campo `sigla` (disabled ou readonly) caso esteja editando o registro "Sem Bolsa".
    - Validação de erros inline com componente `Message` do PrimeVue.
    - Botões de Cancelar e Submeter com estado de carregamento (`loading`).
  - Modal `Dialog` de Confirmação de Exclusão:
    - Diálogo seguro com ícone de alerta e confirmação via `router.delete()`.

### Parte 3: Integração no Menu e Verificação Prática
- Adição do link **Agências de Fomento** no menu de navegação em `resources/js/layouts/AppLayout.vue`.
- Teste ponta a ponta:
  - Listagem inicial com as agências semeadas pelo `AgenciaSeeder` (`Sem bolsa`, `CNPq`, `FAPERJ`, `CAPES`, `UFRJ`);
  - Busca textual por sigla e por nome;
  - Filtragem por tipo (Bolsista, Projeto, Ambos);
  - Cadastro de nova agência de fomento (ex: `FINEP`);
  - Teste de validação de unicidade: tentar cadastrar novamente uma agência com mesma sigla ou nome;
  - Edição de agência recém-criada;
  - Trava do "Sem Bolsa": comprovar que o botão de excluir está desabilitado no frontend e que qualquer tentativa direta é rejeitada pelo backend;
  - Exclusão bem-sucedida da agência recém-criada (ex: `FINEP`);
  - Verificação de controle de acesso (perfil não-administrador recebendo HTTP 403).

---

## 📂 Arquivos Afetados

* **[NEW]** `docs/planos/E3-T6 - CRUD Administrativo de Agencias de Fomento.md`
* **[MODIFY]** `app/Models/Agencia.php`
* **[NEW]** `app/Http/Requests/AgenciaRequest.php`
* **[NEW]** `app/Http/Controllers/AgenciaController.php`
* **[NEW]** `resources/js/pages/admin/agencias/Index.vue`
* **[MODIFY]** `routes/web.php`
* **[MODIFY]** `resources/js/layouts/AppLayout.vue`
* **[MODIFY]** `docs/epics-e-tarefas.md`

---

## 🧪 Verificação Manual

1. Logar como **Super Administrador (Root)** ou **Administrador** e navegar até `/agencias`.
2. Verificar a listagem com as 5 agências semeadas, exibindo as tags de tipo.
3. Testar os filtros: digitar "CNPq" no campo de busca e testar o dropdown de tipo.
4. Clicar em *"Nova Agência"*, preencher Sigla: `FINEP`, Nome: `Financiadora de Estudos e Projetos`, Tipo: `Ambos` e submeter.
5. Tentar cadastrar `FINEP` novamente para verificar o bloqueio de unicidade.
6. Editar o registro `FINEP` e alterar para `Projeto`.
7. Tentar excluir o registro `Sem bolsa` e confirmar que o botão está bloqueado.
8. Excluir o registro `FINEP` criado e verificar mensagem de sucesso.
9. Trocar para perfil **Docente** ou **Discente** via Dev Switcher e tentar acessar `/agencias` para comprovar a resposta HTTP 403.
