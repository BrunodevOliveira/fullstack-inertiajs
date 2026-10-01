<script setup>
import { ref, reactive } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AppHeader from "@/components/AppHeader.vue";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Tag from "primevue/tag";
import Dialog from "primevue/dialog";
import Message from "primevue/message";

const props = defineProps({
  agencias: {
    type: Object,
    required: true,
  },
  tipos: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({ busca: "", tipo: null }),
  },
});

// Estado reativo dos filtros de busca
const filters = reactive({
  busca: props.filters.busca || "",
  tipo: props.filters.tipo || null,
});

// Dispara a filtragem preservando o estado da página
const applyFilters = () => {
  router.get(
    "/agencias",
    {
      busca: filters.busca || undefined,
      tipo: filters.tipo || undefined,
    },
    {
      preserveState: true,
      replace: true,
    }
  );
};

// Limpa todos os filtros ativos
const clearFilters = () => {
  filters.busca = "";
  filters.tipo = null;
  applyFilters();
};

// Paginação remota sincronizada com o backend
const onPage = (event) => {
  router.get(
    "/agencias",
    {
      page: event.page + 1,
      busca: filters.busca || undefined,
      tipo: filters.tipo || undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
};

// Helpers para exibição visual do tipo de agência
const getTipoLabel = (tipo) => {
  const val = typeof tipo === "object" && tipo !== null ? tipo.value : tipo;
  const found = props.tipos.find((t) => t.value === val);
  return found ? found.label : (val ?? "Não informado");
};

const getTipoSeverity = (tipo) => {
  const val = typeof tipo === "object" && tipo !== null ? tipo.value : tipo;
  switch (val) {
    case 1:
      return "info";    // Bolsista
    case 2:
      return "warn";    // Projeto
    case 3:
      return "success"; // Ambos
    default:
      return "secondary";
  }
};

// Controle do Modal de Criação / Edição
const dialogVisible = ref(false);
const editingAgencia = ref(null);

const form = useForm({
  sigla: "",
  nome: "",
  tipo: null,
});

// Abre modal para NOVA agência
const openCreateModal = () => {
  editingAgencia.value = null;
  form.reset();
  form.clearErrors();
  dialogVisible.value = true;
};

// Abre modal para EDITAR agência existente
const openEditModal = (agencia) => {
  editingAgencia.value = agencia;
  form.sigla = agencia.sigla;
  form.nome = agencia.nome;
  form.tipo = typeof agencia.tipo === "object" && agencia.tipo !== null ? agencia.tipo.value : agencia.tipo;
  form.clearErrors();
  dialogVisible.value = true;
};

// Submissão do formulário
const submitForm = () => {
  if (editingAgencia.value) {
    form.put(`/agencias/${editingAgencia.value.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        dialogVisible.value = false;
      },
    });
  } else {
    form.post("/agencias", {
      preserveScroll: true,
      onSuccess: () => {
        dialogVisible.value = false;
        form.reset();
      },
    });
  }
};

// Controle do Modal de Confirmação de Exclusão
const deleteDialogVisible = ref(false);
const agenciaToDelete = ref(null);
const isDeleting = ref(false);

const confirmDelete = (agencia) => {
  if (agencia.is_sem_bolsa) return;
  agenciaToDelete.value = agencia;
  deleteDialogVisible.value = true;
};

const executeDelete = () => {
  if (!agenciaToDelete.value) return;

  isDeleting.value = true;
  router.delete(`/agencias/${agenciaToDelete.value.id}`, {
    preserveScroll: true,
    onFinish: () => {
      isDeleting.value = false;
      deleteDialogVisible.value = false;
      agenciaToDelete.value = null;
    },
  });
};
</script>

<template>
  <div>
    <AppHeader title="Gestão de Agências de Fomento" />

    <div class="space-y-6">
      <!-- Cabeçalho da Página -->
      <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-700"
      >
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
            Gestão de Agências de Fomento
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Cadastre e gerencie as agências de fomento à pesquisa e bolsas institucionais (CNPq, FAPERJ, CAPES, etc.).
          </p>
        </div>
        <div>
          <Button
            label="Nova Agência"
            icon="pi pi-plus"
            severity="primary"
            class="shadow-xs"
            @click="openCreateModal"
          />
        </div>
      </div>

      <!-- Card Principal com Filtros e Tabela -->
      <Card
        class="!bg-white dark:!bg-gray-800 !border !border-gray-200 dark:!border-gray-700 rounded-xl shadow-xs"
      >
        <template #content>
          <!-- Barra de Filtros -->
          <div
            class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between mb-6"
          >
            <div class="flex flex-col sm:flex-row gap-3 flex-1 max-w-2xl">
              <!-- Busca por Sigla ou Nome -->
              <div class="relative flex-1">
                <InputText
                  v-model="filters.busca"
                  placeholder="Buscar por sigla ou nome da agência..."
                  class="w-full"
                  @keydown.enter="applyFilters"
                />
              </div>

              <!-- Filtro por Tipo -->
              <div class="w-full sm:w-56">
                <Select
                  v-model="filters.tipo"
                  :options="tipos"
                  optionLabel="label"
                  optionValue="value"
                  placeholder="Todos os Tipos"
                  showClear
                  class="w-full"
                  @change="applyFilters"
                />
              </div>
            </div>

            <!-- Botões de Ação dos Filtros -->
            <div class="flex gap-2">
              <Button
                icon="pi pi-search"
                label="Filtrar"
                severity="primary"
                @click="applyFilters"
              />
              <Button
                v-if="filters.busca || filters.tipo"
                icon="pi pi-filter-slash"
                severity="secondary"
                text
                title="Limpar Filtros"
                @click="clearFilters"
              />
            </div>
          </div>

          <!-- Tabela de Agências -->
          <DataTable
            :value="agencias.data"
            lazy
            paginator
            :rows="agencias.per_page"
            :totalRecords="agencias.total"
            :first="(agencias.current_page - 1) * agencias.per_page"
            tableStyle="min-width: 40rem"
            @page="onPage"
          >
            <template #empty>
              <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <i class="pi pi-wallet text-4xl mb-3 opacity-40 block" />
                Nenhuma agência de fomento encontrada.
              </div>
            </template>

            <!-- ID -->
            <Column field="id" header="#" style="width: 5rem">
              <template #body="{ data }">
                <span class="font-mono text-xs text-gray-400">{{ data.id }}</span>
              </template>
            </Column>

            <!-- Sigla -->
            <Column header="Sigla" style="width: 9rem">
              <template #body="{ data }">
                <div class="flex items-center gap-2">
                  <i class="pi pi-building text-emerald-600 dark:text-emerald-400 text-sm" />
                  <span class="font-bold text-gray-900 dark:text-gray-100 text-sm">
                    {{ data.sigla }}
                  </span>
                </div>
              </template>
            </Column>

            <!-- Nome da Agência -->
            <Column header="Nome da Agência">
              <template #body="{ data }">
                <div class="flex items-center gap-2">
                  <span class="text-gray-800 dark:text-gray-200 text-sm">
                    {{ data.nome }}
                  </span>
                  <Tag
                    v-if="data.is_sem_bolsa"
                    value="Fixo do Sistema"
                    severity="secondary"
                    class="text-[10px] uppercase font-semibold"
                  />
                </div>
              </template>
            </Column>

            <!-- Tipo de Fomento -->
            <Column header="Tipo" style="width: 13rem">
              <template #body="{ data }">
                <Tag
                  :value="getTipoLabel(data.tipo)"
                  :severity="getTipoSeverity(data.tipo)"
                  class="text-xs"
                />
              </template>
            </Column>

            <!-- Ações -->
            <Column header="Ações" style="width: 8rem; text-align: right">
              <template #body="{ data }">
                <div class="flex items-center justify-end gap-1">
                  <Button
                    icon="pi pi-pencil"
                    severity="secondary"
                    text
                    rounded
                    title="Editar Agência"
                    @click="openEditModal(data)"
                  />
                  <Button
                    icon="pi pi-trash"
                    :severity="data.is_sem_bolsa ? 'secondary' : 'danger'"
                    text
                    rounded
                    :disabled="data.is_sem_bolsa"
                    :title="
                      data.is_sem_bolsa
                        ? 'Registro especial do sistema. Não pode ser excluído.'
                        : 'Excluir Agência'
                    "
                    @click="confirmDelete(data)"
                  />
                </div>
              </template>
            </Column>
          </DataTable>
        </template>
      </Card>
    </div>

    <!-- MODAL DE CRIAÇÃO / EDIÇÃO DE AGÊNCIA -->
    <Dialog
      v-model:visible="dialogVisible"
      modal
      :header="editingAgencia ? 'Editar Agência de Fomento' : 'Nova Agência de Fomento'"
      :style="{ width: '90vw', maxWidth: '480px' }"
    >
      <form @submit.prevent="submitForm" class="space-y-4 pt-2">
        <!-- Campo Sigla -->
        <div>
          <label
            for="agencia_sigla"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"
          >
            Sigla <span class="text-red-500">*</span>
          </label>
          <InputText
            id="agencia_sigla"
            v-model="form.sigla"
            placeholder="Ex: CNPq, FAPERJ, FINEP"
            maxlength="20"
            class="w-full"
            :disabled="editingAgencia?.is_sem_bolsa"
            :invalid="Boolean(form.errors.sigla)"
            autofocus
          />
          <p v-if="editingAgencia?.is_sem_bolsa" class="text-xs text-amber-600 dark:text-amber-400 mt-1">
            A sigla do registro especial 'Sem Bolsa' é fixa e não pode ser alterada.
          </p>
          <p v-else class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Máximo de 20 caracteres.
          </p>
          <Message
            v-if="form.errors.sigla"
            severity="error"
            size="small"
            variant="simple"
            class="mt-1"
          >
            {{ form.errors.sigla }}
          </Message>
        </div>

        <!-- Campo Nome -->
        <div>
          <label
            for="agencia_nome"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"
          >
            Nome Completo da Agência <span class="text-red-500">*</span>
          </label>
          <InputText
            id="agencia_nome"
            v-model="form.nome"
            placeholder="Ex: Conselho Nacional de Desenvolvimento Científico e Tecnológico"
            maxlength="255"
            class="w-full"
            :invalid="Boolean(form.errors.nome)"
          />
          <Message
            v-if="form.errors.nome"
            severity="error"
            size="small"
            variant="simple"
            class="mt-1"
          >
            {{ form.errors.nome }}
          </Message>
        </div>

        <!-- Campo Tipo de Fomento -->
        <div>
          <label
            for="agencia_tipo"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"
          >
            Tipo de Fomento <span class="text-red-500">*</span>
          </label>
          <Select
            id="agencia_tipo"
            v-model="form.tipo"
            :options="tipos"
            optionLabel="label"
            optionValue="value"
            placeholder="Selecione o tipo de fomento"
            class="w-full"
            :invalid="Boolean(form.errors.tipo)"
          />
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Indica se financia apenas bolsistas, projetos ou ambos.
          </p>
          <Message
            v-if="form.errors.tipo"
            severity="error"
            size="small"
            variant="simple"
            class="mt-1"
          >
            {{ form.errors.tipo }}
          </Message>
        </div>

        <!-- Botões do Modal -->
        <div
          class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700"
        >
          <Button
            label="Cancelar"
            severity="secondary"
            text
            @click="dialogVisible = false"
          />
          <Button
            type="submit"
            :label="editingAgencia ? 'Salvar Alterações' : 'Cadastrar Agência'"
            icon="pi pi-check"
            severity="primary"
            :loading="form.processing"
          />
        </div>
      </form>
    </Dialog>

    <!-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO -->
    <Dialog
      v-model:visible="deleteDialogVisible"
      modal
      header="Confirmar Exclusão"
      :style="{ width: '90vw', maxWidth: '440px' }"
    >
      <div class="flex items-start gap-3 py-2">
        <i class="pi pi-exclamation-triangle text-amber-500 text-2xl mt-0.5" />
        <div>
          <p class="text-sm text-gray-700 dark:text-gray-300">
            Tem certeza que deseja excluir a agência de fomento
            <strong>{{ agenciaToDelete?.sigla }} - {{ agenciaToDelete?.nome }}</strong
            >?
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Esta ação removerá a agência da lista ativa do sistema.
          </p>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2">
          <Button
            label="Cancelar"
            severity="secondary"
            text
            :disabled="isDeleting"
            @click="deleteDialogVisible = false"
          />
          <Button
            label="Excluir"
            icon="pi pi-trash"
            severity="danger"
            :loading="isDeleting"
            @click="executeDelete"
          />
        </div>
      </template>
    </Dialog>
  </div>
</template>