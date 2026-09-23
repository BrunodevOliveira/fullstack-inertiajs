# Plano de Implementação - E3-T1: Modelagem e Seeders da Estrutura Acadêmica

Implementar a base relacional da estrutura acadêmica do sistema PINC: criação de migrations, models com relacionamentos estritos em árvore e restrições de integridade referencial, enum de tipo de agência, factories e seeders completos com dados realistas da UFRJ.

---

## 🎯 Proposta Técnica

A tarefa está dividida em 4 partes incrementais:

### Parte 1: Enum e Migrations
- **Enum `AgenciaTipoEnum`** (`app/Enums/AgenciaTipoEnum.php`):
  - Valores: `1` (Bolsista), `2` (Projeto), `3` (Ambos - Bolsa e Projeto).
  - Métodos auxiliares: `label()`, `badgeSeverity()`, `options()`.
- **Migrations** criadas na ordem correta para satisfazer as chaves estrangeiras:
  1. `campuses`: `nome` (varchar 100), timestamps, softDeletes.
  2. `disciplinas`: `nome` (varchar 10, unique - ex: PINC 1 a 4), timestamps, softDeletes.
  3. `cursos`: `nome` (varchar 100), `campus_id` (FK `restrictOnDelete`), `disciplina_id` (FK nullable `nullOnDelete`), `documento_id` (nullable), `email` (nullable), `colaborador` (boolean default false), timestamps, softDeletes.
  4. `departamentos`: `nome` (varchar 100), `curso_id` (FK `restrictOnDelete`), timestamps, softDeletes, índice composto `['curso_id', 'nome']`.
  5. `agencias`: `sigla` (varchar 20), `nome` (varchar 255), `tipo` (unsignedTinyInteger nullable), timestamps, softDeletes.

### Parte 2: Models Eloquent e Relacionamentos
- **`Campus`**:
  - Relacionamento: `cursos(): HasMany`
- **`Disciplina`**:
  - Relacionamento: `cursos(): HasMany`
- **`Curso`**:
  - Relacionamentos: `campus(): BelongsTo`, `disciplina(): BelongsTo`, `departamentos(): HasMany`
  - Cast: `colaborador => boolean`
- **`Departamento`**:
  - Relacionamento: `curso(): BelongsTo`
- **`Agencia`**:
  - Cast: `tipo => AgenciaTipoEnum::class`
  - Helper: `isSemBolsa(): bool`

### Parte 3: Factories e Seeders
- **Factories**: `CampusFactory`, `DisciplinaFactory`, `CursoFactory`, `DepartamentoFactory`, `AgenciaFactory`.
- **Seeders**:
  - `DisciplinaSeeder`: Cadastro ordenado de PINC 1, PINC 2, PINC 3 e PINC 4.
  - `AgenciaSeeder`: Cadastro das principais agências (Sem Bolsa, CNPq, FAPERJ, CAPES, UFRJ).
  - `EstruturaAcademicaSeeder`: Cadastro realista de Campi (Cidade Universitária, Praia Vermelha, Macaé, Caxias), Cursos (Biomedicina, Medicina, Farmácia, etc.) e Departamentos associados.
  - Atualização do `DatabaseSeeder` para invocar os novos seeders.

### Parte 4: Verificação no Tinker e Banco de Dados
- Executar `php artisan migrate:fresh --seed`.
- Validar via Tinker os relacionamentos em árvore (`Campus -> Curso -> Departamento`) e a integridade de chaves estrangeiras.

---

## 📂 Arquivos Afetados

* **[NEW]** `app/Enums/AgenciaTipoEnum.php`
* **[NEW]** `database/migrations/xxxx_create_campuses_table.php`
* **[NEW]** `database/migrations/xxxx_create_disciplinas_table.php`
* **[NEW]** `database/migrations/xxxx_create_cursos_table.php`
* **[NEW]** `database/migrations/xxxx_create_departamentos_table.php`
* **[NEW]** `database/migrations/xxxx_create_agencias_table.php`
* **[NEW]** `app/Models/Campus.php`
* **[NEW]** `app/Models/Disciplina.php`
* **[NEW]** `app/Models/Curso.php`
* **[NEW]** `app/Models/Departamento.php`
* **[NEW]** `app/Models/Agencia.php`
* **[NEW]** `database/factories/CampusFactory.php`
* **[NEW]** `database/factories/DisciplinaFactory.php`
* **[NEW]** `database/factories/CursoFactory.php`
* **[NEW]** `database/factories/DepartamentoFactory.php`
* **[NEW]** `database/factories/AgenciaFactory.php`
* **[NEW]** `database/seeders/DisciplinaSeeder.php`
* **[NEW]** `database/seeders/AgenciaSeeder.php`
* **[NEW]** `database/seeders/EstruturaAcademicaSeeder.php`
* **[MODIFY]** `database/seeders/DatabaseSeeder.php`
* **[MODIFY]** `docs/epics-e-tarefas.md`

---

## 🧪 Verificação Manual

1. Executar `php artisan migrate:fresh --seed`.
2. Validar contagem no Tinker:
   - `Campus::count()`
   - `Curso::count()`
   - `Departamento::count()`
   - `Disciplina::count()` (exatamente 4: PINC 1 a 4)
   - `Agencia::count()` (incluindo "Sem bolsa")
3. Testar no Tinker a navegação em árvore:
   ```php
   $campus = Campus::with('cursos.departamentos')->first();
   $campus->cursos->first()->departamentos;
   ```
4. Testar a proteção contra exclusão (`restrictOnDelete`):
   ```php
   $campus->delete(); // Deve disparar QueryException bloqueando por ter cursos vinculados
   ```

