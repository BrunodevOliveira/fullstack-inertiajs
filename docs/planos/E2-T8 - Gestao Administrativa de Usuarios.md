# Plano de Implementação - E2-T8: Gestão Administrativa de Usuários (Root / Admin)

Implementar o painel administrativo de gestão de usuários do sistema PINC, permitindo que usuários com privilégios de **Super Administrador (Root)** e **Administrador** visualizem a listagem de todos os usuários com filtros avançados, paginação do Eloquent e modal de edição para gerenciar perfis e status (ativo/inativo).

---

## 🎯 Proposta Técnica

### 1. Backend: Controller, FormRequest e Rotas
- Criar Controller `app/Http/Controllers/AdminUsuarioController.php`:
  - `index(Request $request)`:
    - Autorização: Apenas usuários com perfil `Root` ou `Administrador`.
    - Filtros por busca textual (`nome`, `cpf`, `email`), por `perfil_id` e por `situacao` (ativo/inativo).
    - Paginação com Eloquent (`paginate(10)->withQueryString()`) e carregamento ávido (`with('perfis')`).
    - Retorna `Inertia::render('admin/usuarios/Index', [...])` com a coleção de usuários, lista de perfis disponíveis e os filtros aplicados.
  - `update(Request $request, Usuario $usuario)`:
    - Validação: `perfis` (array de IDs válidos na tabela `perfis`), `situacao` (boolean).
    - Regra de segurança: O Root não pode desativar a si mesmo nem remover seu próprio perfil Root.
    - Atualização: `$usuario->perfis()->sync($request->perfis)` e `$usuario->update(['situacao' => $request->situacao])`.
    - Redireciona com mensagem de sucesso.
- Adicionar rotas em `routes/web.php` dentro do grupo `Route::middleware('auth')`:
  - `GET /usuarios` (`usuarios.index`)
  - `PUT /usuarios/{usuario}` (`usuarios.update`)

### 2. Frontend: Página Administrativa com PrimeVue DataTable e Dialog
- Criar `resources/js/pages/admin/usuarios/Index.vue`:
  - Barra de ferramentas / filtros: Campo de busca rápida com debounce, filtro por Perfil (`Select`) e filtro por Situação (`Select`).
  - Tabela `DataTable` do PrimeVue exibindo: Nome / CPF, E-mail, Perfis (com `Tag` colorida), Situação (Badge Ativo/Inativo) e Coluna de Ações.
  - Ações na linha:
    - Botão **Editar** (abre o modal `Dialog`).
    - Botão **Personificar** (visível para o Root personificar o usuário alvo diretamente da tabela).
  - Modal `Dialog` de edição:
    - Seleção múltipla de perfis com `MultiSelect` ou checkboxes.
    - Switch/Toggle para ativar ou desativar o usuário.
    - Integração com `useForm` do Inertia.
  - Paginação remota sincronizada com a URL via `router.get`.

---

## 📂 Arquivos Afetados

* **[NEW]** `app/Http/Controllers/AdminUsuarioController.php`
* **[NEW]** `resources/js/pages/admin/usuarios/Index.vue`
* **[MODIFY]** `routes/web.php`
* **[MODIFY]** `docs/epics-e-tarefas.md`

---

## 🧪 Verificação Manual

1. Logar como **Root** ou **Coordenação / Administrador**.
2. Clicar no menu lateral em **"Usuários"** (`/usuarios`).
3. Testar a busca textual por nome e CPF, e filtrar por perfil (ex: apenas "Docente") e por situação.
4. Clicar no botão de edição de um usuário e alterar seus perfis associados (ex: adicionar perfil de Docente ou desativar temporariamente).
5. Salvar e validar a atualização na tabela e a mensagem de sucesso via Toast.
6. Testar o botão de personificação direto na tabela para um usuário (quando logado como Root).
7. Tentar acessar `/usuarios` logado como Discente e confirmar que o acesso é bloqueado (403 Forbidden).

