<script setup>
import { ref, reactive, computed } from "vue";
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
  departamentos: {
    type: Object,
    required: true,
  },
  campuses: {
    type: Array,
    required: true,
  },
  cursos: {
    type: Array,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ busca: "", curso_id: null, campus_id: null }),
  },
});

// Estado reativo dos filtros
const filters = reactive({
  busca: props.filters.busca || "",
  campus_id: props.filters.campus_id || null,
  curso_id: props.filters.curso_id || null,
});

// Cursos disponíveis para o filtro (se selecionou campus, restringe aos cursos daquele campus)
const availableCursosForFilter = computed(() => {
  if (!filters.campus_id) return props.cursos;
  return props.cursos.filter((c) => c.campus_id === filters.campus_id);
});

// Ao trocar o campus no filtro, limpa o curso caso ele não pertença ao campus selecionado
const onCampusFilterChange = () => {
  if (filters.campus_id && filters.curso_id) {
    const cursoExisteNoCampus = props.cursos.some(
      (c) => c.id === filters.curso_id && c.campus_id === filters.campus_id
    );
    if (!cursoExisteNoCampus) {
      filters.curso_id = null;
    }
  }
  applyFilters();
};

// Cursos formatados com campus para o select do modal de criação/edição
const cursoOptions = computed(() => {
  return props.cursos.map((c) => ({
    id: c.id,
    nomeFormatado: c.campus?.nome ? `${c.nome} (${c.campus.nome})` : c.nome,
  }));
});

// Aplica os filtros combinados (busca textual + campus + curso)
const applyFilters = () => {
  router.get(
    "/departamentos",
    {
      busca: filters.busca || undefined,
      campus_id: filters.campus_id || undefined,
      curso_id: filters.curso_id || undefined,
    },
    {
      preserveState: true,
      replace: true,
    }
  );
};

// Limpa todos os filtros
const clearFilters = () => {
  filters.busca = "";
  filters.campus_id = null;
  filters.curso_id = null;
  applyFilters();
};

// Paginação remota sincronizada
const onPage = (event) => {
  router.get(
    "/departamentos",
    {
      page: event.page + 1,
      busca: filters.busca || undefined,
      campus_id: filters.campus_id || undefined,
      curso_id: filters.curso_id || undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
};

// Controle do Modal de Criação / Edição
const dialogVisible = ref(false);
const editingDepartamento = ref(null);

const form = useForm({
  nome: "",
  curso_id: null,
});

// Abre modal para NOVO departamento
const openCreateModal = () => {
  editingDepartamento.value = null;
  form.reset();
  form.clearErrors();
  dialogVisible.value = true;
};

// Abre modal para EDITAR departamento existente
const openEditModal = (departamento) => {
  editingDepartamento.value = departamento;
  form.nome = departamento.nome;
  form.curso_id = departamento.curso_id;
  form.clearErrors();
  dialogVisible.value = true;
};

// Submissão do formulário
const submitForm = () => {
  if (editingDepartamento.value) {
    form.put(`/departamentos/${editingDepartamento.value.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        dialogVisible.value = false;
      },
    });
  } else {
    form.post("/departamentos", {
      preserveScroll: true,
      onSuccess: () => {
        dialogVisible.value = false;
        form.reset();
      },
    });
  }
};

// Controle do Modal de Exclusão
const deleteDialogVisible = ref(false);
const departamentoToDelete = ref(null);
const isDeleting = ref(false);

const confirmDelete = (departamento) => {
  departamentoToDelete.value = departamento;
  deleteDialogVisible.value = true;
};

const executeDelete = () => {
  if (!departamentoToDelete.value) return;

  isDeleting.value = true;
  router.delete(`/departamentos/${departamentoToDelete.value.id}`, {
    preserveScroll: true,
    onFinish: () => {
      isDeleting.value = false;
      deleteDialogVisible.value = false;
      departamentoToDelete.value = null;
    },
  });
};

const formatDate = (dateString) => {
  if (!dateString) return "—";
  return new Date(dateString).toLocaleDateString("pt-BR", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
};
</script>

<template>
  <div>
    <AppHeader title="Gestão de Departamentos / Laboratórios" />

    <div class="space-y-6">
      <!-- Cabeçalho da Página -->
      <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-700"
      >
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
            Departamentos & Laboratórios
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Cadastre e gerencie os departamentos e laboratórios acadêmicos vinculados aos
            cursos.
          </p>
        </div>
        <div>
          <Button
            label="Novo Departamento"
            icon="pi pi-plus"
            severity="primary"
            class="shadow-xs"
            @click="openCreateModal"
          />
        </div>
      </div>

      <!-- Card Principal com Barra de Filtros e Tabela -->
      <Card
        class="!bg-white dark:!bg-gray-800 !border !border-gray-200 dark:!border-gray-700 rounded-xl shadow-xs"
      >
        <template #content>
          <!-- Barra de Filtros -->
          <div
            class="flex flex-col lg:flex-row gap-3 items-stretch lg:items-center justify-between mb-6"
          >
            <div class="flex flex-col sm:flex-row gap-3 flex-1 flex-wrap">
              <!-- Busca por Nome -->
              <div class="relative flex-1 min-w-[200px]">
                <InputText
                  v-model="filters.busca"
                  placeholder="Buscar por departamento/laboratório..."
                  class="w-full"
                  @keydown.enter="applyFilters"
                />
              </div>

              <!-- Filtro por Campus -->
              <Select
                v-model="filters.campus_id"
                :options="campuses"
                optionLabel="nome"
                optionValue="id"
                placeholder="Filtrar por Campus"
                showClear
                class="w-full sm:w-56"
                @change="onCampusFilterChange"
              />

              <!-- Filtro por Curso -->
              <Select
                v-model="filters.curso_id"
                :options="availableCursosForFilter"
                optionLabel="nome"
                optionValue="id"
                placeholder="Filtrar por Curso"
                showClear
                class="w-full sm:w-64"
                @change="applyFilters"
              />
            </div>

            <!-- Botões de Ação do Filtro -->
            <div class="flex gap-2 justify-end">
              <Button
                icon="pi pi-search"
                label="Buscar"
                severity="primary"
                @click="applyFilters"
              />
              <Button
                v-if="filters.busca || filters.campus_id || filters.curso_id"
                icon="pi pi-filter-slash"
                severity="secondary"
                text
                title="Limpar Filtros"
                @click="clearFilters"
              />
            </div>
          </div>

          <!-- Tabela de Departamentos -->
          <DataTable
            :value="departamentos.data"
            lazy
            paginator
            :rows="departamentos.per_page"
            :totalRecords="departamentos.total"
            :first="(departamentos.current_page - 1) * departamentos.per_page"
            tableStyle="min-width: 50rem"
            @page="onPage"
          >
            <template #empty>
              <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <i class="pi pi-sitemap text-4xl mb-3 opacity-40 block" />
                Nenhum departamento ou laboratório encontrado.
              </div>
            </template>

            <!-- ID -->
            <Column field="id" header="#" style="width: 4rem">
              <template #body="{ data }">
                <span class="font-mono text-xs text-gray-400">{{ data.id }}</span>
              </template>
            </Column>

            <!-- Nome do Departamento / Laboratório -->
            <Column header="Departamento / Laboratório">
              <template #body="{ data }">
                <div class="flex items-center gap-2">
                  <i class="pi pi-sitemap text-indigo-600 dark:text-indigo-400 text-sm" />
                  <span class="font-semibold text-gray-900 dark:text-gray-100 text-sm">
                    {{ data.nome }}
                  </span>
                </div>
              </template>
            </Column>

            <!-- Curso Vinculado -->
            <Column header="Curso Vinculado" style="width: 18rem">
              <template #body="{ data }">
                <div
                  class="flex items-center gap-1.5 text-xs text-gray-700 dark:text-gray-300"
                >
                  <i class="pi pi-book text-gray-400 text-xs" />
                  <span
                    class="font-medium truncate max-w-[16rem]"
                    :title="data.curso?.nome"
                  >
                    {{ data.curso?.nome || "—" }}
                  </span>
                </div>
              </template>
            </Column>

            <!-- Campus -->
            <Column header="Campus" style="width: 14rem">
              <template #body="{ data }">
                <Tag
                  :value="data.curso?.campus?.nome || '—'"
                  severity="info"
                  class="text-xs"
                />
              </template>
            </Column>

            <!-- Data de Cadastro -->
            <Column header="Cadastrado em" style="width: 9rem">
              <template #body="{ data }">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatDate(data.created_at) }}
                </span>
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
                    title="Editar Departamento"
                    @click="openEditModal(data)"
                  />
                  <Button
                    icon="pi pi-trash"
                    severity="danger"
                    text
                    rounded
                    title="Excluir Departamento"
                    @click="confirmDelete(data)"
                  />
                </div>
              </template>
            </Column>
          </DataTable>
        </template>
      </Card>
    </div>

    <!-- MODAL DE CRIAÇÃO / EDIÇÃO DE DEPARTAMENTO -->
    <Dialog
      v-model:visible="dialogVisible"
      modal
      :header="
        editingDepartamento
          ? 'Editar Departamento / Laboratório'
          : 'Novo Departamento / Laboratório'
      "
      :style="{ width: '90vw', maxWidth: '520px' }"
    >
      <form @submit.prevent="submitForm" class="space-y-4 pt-2">
        <!-- Nome -->
        <div>
          <label
            for="depto_nome"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"
          >
            Nome do Departamento / Laboratório <span class="text-red-500">*</span>
          </label>
          <InputText
            id="depto_nome"
            v-model="form.nome"
            placeholder="Ex: Laboratório de Biologia Molecular"
            class="w-full"
            :invalid="Boolean(form.errors.nome)"
            autofocus
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

        <!-- Curso Vinculado -->
        <div>
          <label
            for="depto_curso"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"
          >
            Curso Vinculado <span class="text-red-500">*</span>
          </label>
          <Select
            id="depto_curso"
            v-model="form.curso_id"
            :options="cursoOptions"
            optionLabel="nomeFormatado"
            optionValue="id"
            placeholder="Selecione o curso ao qual está subordinado"
            class="w-full"
            filter
            :invalid="Boolean(form.errors.curso_id)"
          />
          <small class="text-xs text-gray-500 dark:text-gray-400 mt-1 block">
            Selecione o curso acadêmico que sedia este departamento ou laboratório.
          </small>
          <Message
            v-if="form.errors.curso_id"
            severity="error"
            size="small"
            variant="simple"
            class="mt-1"
          >
            {{ form.errors.curso_id }}
          </Message>
        </div>

        <!-- Botões de Ação do Modal -->
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
            :label="editingDepartamento ? 'Salvar Alterações' : 'Cadastrar Departamento'"
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
      :style="{ width: '90vw', maxWidth: '420px' }"
    >
      <div class="flex items-start gap-3 py-2">
        <i class="pi pi-exclamation-triangle text-amber-500 text-2xl mt-0.5" />
        <div>
          <p class="text-sm text-gray-700 dark:text-gray-300">
            Tem certeza que deseja excluir o departamento/laboratório
            <strong>{{ departamentoToDelete?.nome }}</strong
            >?
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Esta ação removerá o departamento da lista ativa do sistema.
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
