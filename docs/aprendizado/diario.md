# Diário de Aprendizagem - PINC

Registro de evolução técnica, lacunas identificadas e fila de repetição espaçada.

---

## E4-T1: Modelagem da Tabela `projetos` e Relacionamento do Responsável
- **Data de Conclusão:** 01/10/2026
- **Status:** Concluído com sucesso (3 testes passando, 9 asserções)
- **Conceitos Praticados:**
  - Criação de migration com tipos estritos (`unsignedInteger`, `text`, `string(300)`, `string(500)`), soft deletes e integridade relacional (`restrictOnDelete`).
  - Ordem correta de modificadores de coluna e chave estrangeira (`nullable()->constrained()`).
  - Model Eloquent com `$fillable`, `$casts` e traits (`SoftDeletes`, `HasFactory`).
  - Regra de Ouro dos relacionamentos: quem guarda a chave estrangeira na própria tabela usa `belongsTo`; o inverso usa `hasMany`.
  - Relacionamentos com suporte a registros excluídos historicamente via `withTrashed()`.
  - Local Scopes (`scopeAtivos`, `scopeArquivados`) vs Global Scopes (como o `SoftDeletingScope`).
  - Factories conectadas entre models (`ProjetoFactory` gerando `Departamento` e `Usuario`) e estados de factory (`arquivado()`).
  - Primeiro teste automatizado com PHPUnit e `RefreshDatabase`.
- **Lacunas Superadas:**
  - Diferença entre `HasMany` e `BelongsTo` (quem guarda a FK).
  - Como funcionam e para que servem os Local Scopes (centralização de regras de consulta sem duplicação em controllers).
- **Fila de Revisão Espaçada:**
  - `belongsTo` vs `hasMany` e parâmetros explícitos (D+1: 02/10/2026, D+7: 08/10/2026).
  - Local Scopes e manipulação de `$query` (D+1: 02/10/2026, D+7: 08/10/2026, D+21: 22/10/2026).
  - Asserções no PHPUnit (`assertDatabaseHas`, `assertInstanceOf`, `assertCount`, `assertSoftDeleted`) (D+1: 02/10/2026).
