# Roadmap de Desenvolvimento & Aprendizado: PINC
**Stack:** Laravel 12 | PHP 8.3+ | Vue 3 (Composition API) | Inertia.js | PrimeVue v4 | Tailwind CSS v4

---

## 🧭 Visão Geral dos Épicos

```
[Épico 1: Fundação & Design System] ──► [Épico 2: Autenticação & Perfis] ──► [Épico 3: Estrutura Acadêmica]
                                                                                              │
[Épico 6: Templates & Avaliações] ◄── [Épico 5: Ciclo de Participantes] ◄── [Épico 4: Gestão de Projetos]
         │
         ▼
[Épico 7: Comunicação, Certificados & Polimento]
```

---

## 🌉 Mapa de Mentoria: Tarefas Pendentes
Consulte este mapa como disparador socrático para levantar pistas e casos adversos antes de implementar cada tarefa.

| Tarefa | Ponte Angular → Laravel/Vue | Casos Adversos a Testar |
| :--- | :--- | :--- |
| **E4-T1** | Models Eloquent ≈ Interfaces TS + Data Layer com ORM ativo | Responsável removido (soft delete); escopos ativos/arquivados; N+1 em listagens. |
| **E4-T2** | Policy ≈ CanActivate no servidor (verdade final; guard no front é UX) | Docente sem Lattes; Lattes em branco; POST direto de aluno via terminal/cURL. |
| **E4-T3** | `useForm` ≈ Reactive Forms; Dropdown encadeado ≈ `valueChanges` com `watch` | Trocar curso após escolher departamento; vagas negativas ou não inteiras; mass assignment. |
| **E4-T4** | `DB::transaction` ≈ Operação atômica "tudo ou nada"; Observer ≈ Domain Event | Forçar exceção ao criar participante e garantir rollback total do projeto. |
| **E4-T5** | Query params + `preserveState` ≈ `ActivatedRoute.queryParams` | Busca com `%` ou `_` (curingas SQL); página além do limite; XSS no modal. |
| **E4-T6** | Visibilidade por perfil ≈ Route Guards com filtros de escopo no backend | Docente alterando ID na URL para inspecionar projeto alheio (IDOR); conflito admin vs docente. |
| **E4-T7** | Validação cruzada (vagas vs ativos) ≈ Custom Validator de `FormGroup` | Redução de vagas concorrente com indicação (race condition); idempotência ao arquivar. |
| **E5-T1** | Enum com cast Eloquent ≈ Enum TS persistido no banco | Duplicidade do par `(projeto_id, usuario_id)`; valores de status inválidos no payload. |
| **E5-T2** | Accordion/Tabs ≈ `ngSwitch`/`ng-template`; Props/Emits ≈ `@Input`/@Output | Listagens vazias; botões ocultos na UI mas com rotas acessíveis no backend. |
| **E5-T3** | Service Laravel ≈ `@Injectable`; Service Container ≈ Angular Injector | Aceitar duas vezes; aceitar sem vaga livre; transição ilegal (Histórico → Ativo). |
| **E5-T4** | Modal de busca com debounce ≈ Autocomplete com `debounceTime` (RxJS) | CPF não cadastrado; CPF com/sem máscara; CPF de professor; aluno já ativo ou sem vagas. |
| **E5-T5** | Event/Listener Laravel ≈ Subject/EventEmitter | Falha ao instanciar avaliação pós-aceite; duplo clique rápido; usuário sem privilégio. |
| **E5-T6** | Máquina de estados ≈ Store com transições finitas | Reversibilidade de Saindo vs irreversibilidade de Histórico; liberação atômica de vaga. |
| **E6-T1** | Coluna JSON + Cast Eloquent ≈ Interface TS mapeada para objeto | JSON nulo/corrompido; alteração de template com relatórios existentes (versionamento). |
| **E6-T2** | Esquema de formulário dinâmico ≈ Configuração declarativa de formulário | Tipo de campo desconhecido; campo obrigatório sem rótulo configurado. |
| **E6-T3** | `<component :is>` ≈ `ngComponentOutlet`; `computed` ≈ `computed()` de Signals | Operação aritmética com valor nulo; mutação acidental da matriz JSON original. |
| **E6-T4** | Validação condicional (rascunho vs envio) ≈ Validadores dinâmicos | Edição após envio; aluno B manipulando payload do aluno A; payload com campos extras. |
| **E6-T5** | Upload Inertia ≈ `HttpClient` com `FormData` | Extensão `.php` disfarçada em `.pdf` (validar MIME real); storage privado sem vazamento. |
| **E6-T6** | Policy + formulário do avaliador | Docente não responsável avaliando; notas fora do range pré-estabelecido. |
| **E6-T7** | Fluxo administrativo ≈ Guard + Resolver | Marcar "Aprovado" sem relatório enviado; retificação após geração de certificado. |
| **E6-T8** | PrimeVue DataTable Lazy ≈ MatTable com paginação remota | Combinação de filtros zerada; ordenação por coluna arbitrária (whitelist obrigatória); N+1. |
| **E7-T1** | Interface PHP + Container binding ≈ `InjectionToken` com `useClass` | Tentativa de reemissão de certificado; lote com falhas parciais; emitir para reprovado. |
| **E7-T2** | Rota pública sem middleware de auth ≈ Rota sem CanActivate | Hash inexistente (404 padronizado); brute force/rate limiting; vazamento de dados (LGPD). |
| **E7-T3** | Laravel Queues ≈ Processamento assíncrono isolado do ciclo HTTP | Respeito à flag `desabilitar_email`; falha de jobs (`failed_jobs`); retentativas e throttling. |
| **E7-T4** | Exportação de arquivos ≈ Download de Blob via streaming | CSV injection (células iniciando com `=`, `+`, `-`, `@`); estouro de memória (`chunk`/`stream`). |
| **E7-T5** | Handler de Exceções Global ≈ `HttpInterceptor` / `ErrorHandler` | Tratamento amigável do erro 419 (CSRF expirado); 500 sem vazar stack trace; 403 vs 404. |

---

## 📋 Detalhamento dos Épicos e Backlog

### 🏛️ ÉPICO 1: Fundação, Ambiente e Design System
- [x] **E1-T1:** Downgrade e Estabilização do PrimeVue v4  
- [x] **E1-T2:** Configuração do Banco de Dados MySQL / MariaDB  
- [x] **E1-T3:** Instalação e Configuração dos PrimeIcons  
- [x] **E1-T4:** Criação do Layout Mestre (`AppLayout.vue`)  
  *Commit:* `feat: Adicionado Menu principal e utilização de slot do vue`
- [x] **E1-T5:** Sistema Global de Notificações (Flash Messages & Toast)  
  *Commit:* `feat: adicionado Toast e compartilhamento de mensagem entre endpoints com Middleware HandleInertiaRequests`

---

### 👤 ÉPICO 2: Autenticação, Perfis e Gestão de Usuários
- [x] **E2-T1:** Modelagem da Tabela de Perfis e Enum Tipado  
  *Commit:* `Criação de Enum, Migration, Model e Seeder para Perfil`
- [x] **E2-T2:** Modelagem da Tabela `usuarios` e Tabela Pivô `perfil_usuario`  
- [x] **E2-T3:** Modelagem Complementar: Tabela `alunos` e `log_users`  
- [x] **E2-T4:** Seeder de Usuários de Demonstração para Cada Perfil  
- [x] **E2-T5:** Sistema de Login Tradicional e Dev Switcher  
- [x] **E2-T6:** Funcionalidade de Impersonation (Root assumindo identidade)  
  *Commit:* `feat: Implementada funcionalidade de Impersonation para o perfil Root com banner de aviso e retorno seguro`
- [x] **E2-T7:** Tela "Meu Perfil" (Dados Pessoais & Lattes)  
  *Commit:* `feat: Implementada tela Meu Perfil com consulta institucional e edicao de dados de contato`
- [x] **E2-T8:** Gestão Administrativa de Usuários (Root / Admin)  
  *Commit:* `feat: Implementada gestao administrativa de usuarios com DataTable, filtros avancados e modal de edicao de perfis`

---

### 📚 ÉPICO 3: Estrutura Acadêmica (Cadastros Administrativos)
- [x] **E3-T1:** Modelagem e Seeders da Estrutura Acadêmica (Campus, Curso, Depto, PINC, Agência)  
  *Commit:* `feat: Implementada modelagem e seeders da estrutura academica com integridade referencial estrita e enums`
- [x] **E3-T2:** CRUD Administrativo de Campus com Bloqueio de Dependências  
  *Commit:* `feat: Implementado CRUD administrativo de Campus com PrimeVue DataTable, Dialog reativo e bloqueio de exclusao por dependencia`
- [x] **E3-T3:** CRUD Administrativo de Cursos  
- [x] **E3-T4:** CRUD Administrativo de Departamentos / Laboratórios  
  *Commit:* `feat: Implementado CRUD administrativo de Departamentos e Laboratorios com unicidade composta e filtros encadeados`
- [x] **E3-T5:** CRUD Administrativo de Disciplinas / Períodos (PINC)  
  *Commit:* `feat: Implementado CRUD administrativo de Disciplinas e Periodos (PINC) com ordenacao natural e trava de exclusao por dependencia`
- [x] **E3-T6:** CRUD Administrativo de Agências de Fomento  
  *Commit:* `feat: Implementado CRUD administrativo de Agencias de Fomento com filtros por tipo, unicidade e protecao do registro Sem Bolsa`

---

### 🔬 ÉPICO 4: Gestão de Projetos de Pesquisa
- [x] **E4-T1:** Modelagem da Tabela `projetos` e Relacionamento do Responsável (soft deletes, escopos ativos/arquivados).  
  *Commit:* `feat: Implementada modelagem da tabela projetos, model Projeto, relacionamentos, escopos e testes`
- [ ] **E4-T2:** Regras de Negócio de Criação de Projeto (Policy docente + validação de link Lattes).  
- [ ] **E4-T3:** Formulário de Cadastro e Edição de Projetos (Dropdown encadeado Curso → Depto, validação de vagas).  
- [ ] **E4-T4:** Associação Automática do Docente Responsável como Participante (`DB::transaction` / Domain Action).  
- [ ] **E4-T5:** Catálogo Público de Projetos na Home (Filtros por query params, `preserveState`, busca textual, paginação).  
- [ ] **E4-T6:** Listagem Autenticada de Projetos (Visibilidade segmentada: Meus Projetos vs Todos os Projetos).  
- [ ] **E4-T7:** Arquivamento e Regra de Capacidade de Vagas (Bloqueio de redução abaixo dos ativos).

---

### 🤝 ÉPICO 5: Ciclo de Vida de Participantes e Indicações
- [ ] **E5-T1:** Modelagem da Pivô `projeto_usuario` e Enum `ParticipanteStatusEnum`.  
- [ ] **E5-T2:** Tela de Detalhes do Projeto com Acordeões/Tabs por Papel e Status de Participante.  
- [ ] **E5-T3:** Domain Service de Transição de Estados (`ProjetoParticipanteService` com transações atômicas).  
- [ ] **E5-T4:** Fluxo de Indicação de Discente com Modal de Busca e Validação de Vagas.  
- [ ] **E5-T5:** Aceite e Rejeição de Indicações com Criação Automática do Registro de Avaliação.  
- [ ] **E5-T6:** Fluxo de Desligamento de Discente (Saindo, Manter ou Mover para Histórico).

---

### 📝 ÉPICO 6: Templates Dinâmicos e Avaliações
- [ ] **E6-T1:** Modelagem de Templates de Relatório (`relatorios`) e Avaliações (`avaliacoes`) com colunas JSON e casts.  
- [ ] **E6-T2:** Seeder de Esquemas de Formulário Padrão por Curso.  
- [ ] **E6-T3:** Motor de Renderização de Formulários Dinâmicos em Vue 3 (`DynamicReportForm.vue` com `<component :is>`).  
- [ ] **E6-T4:** Preenchimento de Relatório pelo Discente (Rascunho vs Submissão Definitiva).  
- [ ] **E6-T5:** Gerenciamento de Anexos Científicos (Storage seguro, validação de MIME real e cotas).  
- [ ] **E6-T6:** Avaliação de Desempenho pelo Responsável do Projeto.  
- [ ] **E6-T7:** Interface de Julgamento Administrativo (Aprovação e Metadados Institucionais).  
- [ ] **E6-T8:** Painéis de Consulta com DataTable Lazy (Paginação remota e filtros compostos).

---

### 🚀 ÉPICO 7: Comunicação, Certificados e Polimento
- [ ] **E7-T1:** Emissão de Certificados com Service Pattern e Identificador UUID Único.  
- [ ] **E7-T2:** Consulta Pública e Validação de Autenticidade de Certificados via Hash.  
- [ ] **E7-T3:** Filas Assíncronas para Notificações e E-mails em Massa (Database Queues e Mailables).  
- [ ] **E7-T4:** Exportação de Dados em CSV/XLSX com Proteção Contra Injection e Streaming de Memória.  
- [ ] **E7-T5:** Manipulador Global de Erros HTTP (401, 403, 404, 419, 500) e Telas Amigáveis em Vue.

---

## 🧠 Checkpoints de Retenção
- **Checkpoint Zero (Antes da E4-T1):** Blind rebuild de um CRUD simples (ex.: Agência ou Campus) sem auxílio de IA.  
- **Fim do Épico 4:** Blind rebuild de listagem paginada com busca por query params e Policy.  
- **Fim do Épico 5:** Adicionar novo estado à máquina de estados de participantes sem consulta prévia.  
- **Fim do Épico 6:** Adicionar novo tipo de input ao motor dinâmico e esquema JSON.  
- **Fim do Épico 7:** Criar um Mailable em fila com tratamento de falhas.
