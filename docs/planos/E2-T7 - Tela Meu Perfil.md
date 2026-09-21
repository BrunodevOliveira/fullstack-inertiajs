# Plano de Implementação - E2-T7: Tela "Meu Perfil" (Dados Pessoais)

Implementar a página de perfil pessoal onde o usuário autenticado pode consultar seus dados cadastrais e acadêmicos institucionais (somente leitura) e atualizar suas informações de contato e currículo acadêmico (telefone, link do Lattes e nome social).

---

## 🎯 Proposta Técnica

### 1. Backend: FormRequest, Controller e Rotas
- Criar FormRequest `app/Http/Requests/UpdateProfileRequest.php`:
  - Regras de validação para `telefone` (nullable, max 20), `lattes` (nullable, url, max 255) e `nome_social` (nullable, max 255).
- Criar Controller `app/Http/Controllers/ProfileController.php`:
  - `edit(Request $request)`: Renderiza a página `profile/Edit` via Inertia com os dados do usuário autenticado (incluindo perfis e relacionamento `aluno`).
  - `update(UpdateProfileRequest $request)`: Atualiza os dados permitidos (`nome_social`, `telefone`, `lattes`) no modelo `Usuario` e redireciona com mensagem flash de sucesso.
- Adicionar rotas em `routes/web.php` dentro do grupo `Route::middleware('auth')`:
  - `GET /meu-perfil` (`profile.edit`)
  - `PUT /meu-perfil` (`profile.update`)

### 2. Frontend: Página Vue com PrimeVue e Inertia `useForm`
- Criar `resources/js/pages/profile/Edit.vue`:
  - **Seção de Identificação / Dados Institucionais (Read-Only):** Nome completo, CPF, E-mail institucional, Perfis com Tags PrimeVue, SIAPE (se servidor) e Dados de Aluno/SIRA/Curso (se discente).
  - **Seção Editável (Formulário Reativo):** Nome Social, Telefone com máscara/validação e Link do Currículo Lattes com validação de URL.
  - Gerenciamento de estado com `useForm` do Inertia, exibindo mensagens de erro inline e estado de processamento no botão de salvar.
- Atualizar `resources/js/layouts/AppLayout.vue`:
  - Adicionar atalho para "Meu Perfil" no cabeçalho onde o usuário visualiza seu nome.

---

## 📂 Arquivos Afetados

* **[NEW]** `app/Http/Requests/UpdateProfileRequest.php`
* **[NEW]** `app/Http/Controllers/ProfileController.php`
* **[NEW]** `resources/js/pages/profile/Edit.vue`
* **[MODIFY]** `routes/web.php`
* **[MODIFY]** `resources/js/layouts/AppLayout.vue`

---

## 🧪 Verificação Manual

1. Logar com diferentes perfis (**Root**, **Docente**, **Discente Aluno**).
2. Acessar a tela **"Meu Perfil"** clicando no nome de usuário no topo.
3. Verificar a exibição correta dos dados específicos de cada perfil (ex: Curso/SIRA para aluno, SIAPE/Lattes para docente).
4. Editar o telefone e a URL do Lattes, salvar o formulário e confirmar a atualização no banco com a mensagem Toast de sucesso.
5. Testar validações de erro (ex: URL inválida no Lattes).

