# Plano de Implementação - E2-T6: Funcionalidade de Impersonation

Permitir que o usuário com perfil **Root (Super Administrador)** assuma a identidade de qualquer outro usuário do sistema (impersonation), mantendo o rastro da sessão original para permitir retornar à conta Root a qualquer momento com um clique no banner de aviso no topo da aplicação.

---

## 🎯 Proposta Técnica

### 1. Backend: Controlador e Rotas de Impersonation
- Criar `app/Http/Controllers/ImpersonationController.php`:
  - `start(Request $request, Usuario $usuario)`:
    - Validação de autorização: apenas usuários com perfil `PerfilEnum::Root` podem iniciar a personificação.
    - Validação de integridade: não permitir personificar a si mesmo nem usuários inativos.
    - Armazenar o ID do usuário original na sessão: `session()->put('impersonator_id', Auth::id())`.
    - Realizar o login como o usuário alvo: `Auth::login($usuario)`.
    - Redirecionar com flash message de sucesso informando que a identidade foi assumida.
  - `leave(Request $request)`:
    - Verificar se a sessão possui a chave `impersonator_id`.
    - Recuperar e remover o ID da sessão (`session()->pull('impersonator_id')`).
    - Restaurar o login do Root original: `Auth::login($originalUser)`.
    - Redirecionar com flash message informando o retorno ao perfil original.
- Adicionar rotas em `routes/web.php` dentro do grupo autenticado (`Route::middleware('auth')`):
  - `POST /impersonar/{usuario}` (`impersonate.start`)
  - `POST /impersonar/sair` (`impersonate.leave`)

### 2. Compartilhamento de Estado no Inertia
- Atualizar `app/Http/Middleware/HandleInertiaRequests.php` para compartilhar dados de personificação com o frontend Vue:
  - `auth.impersonator`: `['id' => ..., 'nome' => ...]` quando `session()->has('impersonator_id')`.

### 3. Frontend: Banner de Personificação e Gatilho no DevSwitcher
- Atualizar `resources/js/layouts/AppLayout.vue`:
  - Adicionar um banner fixo no topo com destaque visual (alerta âmbar/vermelho), visível sempre que `page.props.auth?.impersonator` existir:
    - Ícone e texto: *"Modo de Personificação Ativo: Você está navegando como **[Nome]** (Sessão iniciada por **[Nome Root]**)."*
    - Botão estilizado de ação rápida: *"Sair da Personificação"* disparando requisição POST para a rota `impersonate.leave`.
- Atualizar `resources/js/components/DevSwitcher.vue`:
  - Quando logado como Root (e não estiver personificando), disponibilizar botões de "Personificar" para os demais usuários de desenvolvimento, permitindo testar e demonstrar a funcionalidade com 1 clique.

---

## 📂 Arquivos Afetados

* **[NEW]** `app/Http/Controllers/ImpersonationController.php`
* **[MODIFY]** `routes/web.php`
* **[MODIFY]** `app/Http/Middleware/HandleInertiaRequests.php`
* **[MODIFY]** `resources/js/layouts/AppLayout.vue`
* **[MODIFY]** `resources/js/components/DevSwitcher.vue`
* **[MODIFY]** `docs/epics-e-tarefas.md`

---

## 🧪 Verificação Manual

1. Logar no sistema como **Root (Super Admin)**.
2. Abrir o Dev Switcher e clicar para personificar outro usuário (ex: **Discente Aluno** ou **Docente**).
3. Verificar a aparição do banner de alerta amarelo no topo da tela com as informações da personificação.
4. Navegar pela aplicação e observar que as permissões e dados no cabeçalho pertencem ao usuário personificado.
5. Clicar no botão **"Sair da Personificação"** no banner e confirmar o retorno seguro ao perfil Root.

