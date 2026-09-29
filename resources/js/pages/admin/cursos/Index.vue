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
import ToggleSwitch from "primevue/toggleswitch";
import Dialog from "primevue/dialog";
import Message from "primevue/message";

const props = defineProps({
  cursos: {
    type: Object,
    required: true,
  },
  campuses: {
    type: Array,
    required: true,
  },
  disciplinas: {
    type: Array,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ busca: "", campus_id: null }),
  },
});

// Estado reativo dos filtros de busca
const filters = reactive({
  busca: props.filters.busca || "",
  campus_id: props.filters.campus_id || null,
});

// Aplica os filtros combinados (busca textual + filtro por campus)
const applyFilters = () => {
  router.get(
    "/cursos",
    {
      busca: filters.busca || undefined,
      campus_id: filters.campus_id || undefined,
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
  applyFilters();
};

// Paginação remota sincronizada
const onPage = (event) => {
  router.get(
    "/cursos",
    {
      page: event.page + 1,
      busca: filters.busca || undefined,
      campus_id: filters.campus_id || undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
};

// Controle do Modal de Criação / Edição
const dialogVisible = ref(false);
const editingCurso = ref(null);

const form = useForm({
  nome: "",
  campus_id: null,
  disciplina_id: null,
  email: "",
  colaborador: false,
});

// Abre modal para NOVO curso
const openCreateModal = () => {
  editingCurso.value = null;
  form.reset();
  form.clearErrors();
  dialogVisible.value = true;
};

// Abre modal para EDITAR curso existente
const openEditModal = (curso) => {
  editingCurso.value = curso;
  form.nome = curso.nome;
  form.campus_id = curso.campus_id;
  form.disciplina_id = curso.disciplina_id;
  form.email = curso.email || "";
  form.colaborador = Boolean(curso.colaborador);
  form.clearErrors();
  dialogVisible.value = true;
};

// Submissão do formulário
const submitForm = () => {
  if (editingCurso.value) {
    form.put(`/cursos/${editingCurso.value.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        dialogVisible.value = false;
      },
    });
  } else {
    form.post("/cursos", {
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
const cursoToDelete = ref(null);
const isDeleting = ref(false);

const confirmDelete = (curso) => {
  cursoToDelete.value = curso;
  deleteDialogVisible.value = true;
};

const executeDelete = () => {
  if (!cursoToDelete.value) return;

  isDeleting.value = true;
  router.delete(`/cursos/${cursoToDelete.value.id}`, {
    preserveScroll: true,
    onFinish: () => {
      isDeleting.value = false;
      deleteDialogVisible.value = false;
      cursoToDelete.value = null;
    },
  });
};
</script>

<template>
  <div>
    <AppHeader title="Gestão de Cursos" />

    <div class="space-y-6">
      <!-- Cabeçalho da Página -->
      <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-700"
      >
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
            Gestão de Cursos
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Cadastre e gerencie os cursos de graduação e pós-graduação vinculados aos campi.
          </p>
        </div>
        <div>
          <Button
            label="Novo Curso"
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
            class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between mb-6"
          >
            <div class="flex flex-col sm:flex-row gap-3 flex-1 max-w-2xl">
              <!-- Busca por Nome -->
              <div class="relative flex-1">
                <InputText
                  v-model="filters.busca"
                  placeholder="Buscar por nome do curso..."
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
                class="w-full sm:w-64"
                @change="applyFilters"
              />
            </div>

            <!-- Botões de Ação de Filtro -->
            <div class="flex gap-2">
              <Button
                icon="pi pi-search"
                label="Buscar"
                severity="primary"
                @click="applyFilters"
              />
              <Button
                v-if="filters.busca || filters.campus_id"
                icon="pi pi-filter-slash"
                severity="secondary"
                text
                title="Limpar Filtros"
                @click="clearFilters"
              />
            </div>
          </div>

          <!-- Tabela de Cursos -->
          <DataTable
            :value="cursos.data"
            lazy
            paginator
            :rows="cursos.per_page"
            :totalRecords="cursos.total"
            :first="(cursos.current_page - 1) * cursos.per_page"
            tableStyle="min-width: 50rem"
            @page="onPage"
          >
            <template #empty>
              <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <i class="pi pi-book text-4xl mb-3 opacity-40 block" />
                Nenhum curso encontrado.
              </div>
            </template>

            <!-- ID -->
            <Column field="id" header="#" style="width: 4rem">
              <template #body="{ data }">
                <span class="font-mono text-xs text-gray-400">{{ data.id }}</span>
              </template>
            </Column>

            <!-- Nome do Curso -->
            <Column header="Nome do Curso">
              <template #body="{ data }">
                <div class="flex items-center gap-2">
                  <i class="pi pi-book text-emerald-600 dark:text-emerald-400 text-sm" />
                  <span class="font-semibold text-gray-900 dark:text-gray-100 text-sm">
                    {{ data.nome }}
                  </span>
                </div>
              </template>
            </Column>

            <!-- Campus -->
            <Column header="Campus" style="width: 14rem">
              <template #body="{ data }">
                <Tag
                  :value="data.campus?.nome || '—'"
                  severity="info"
                  class="text-xs"
                />
              </template>
            </Column>

            <!-- Disciplina de Referência -->
            <Column header="Disciplina Ref." style="width: 10rem">
              <template #body="{ data }">
                <span class="text-xs text-gray-600 dark:text-gray-300 font-medium">
                  {{ data.disciplina?.nome || '—' }}
                </span>
              </template>
            </Column>

            <!-- E-mail Institucional -->
            <Column header="E-mail" style="width: 14rem">
              <template #body="{ data }">
                <a
                  v-if="data.email"
                  :href="`mailto:${data.email}`"
                  class="text-xs text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1"
                >
                  <i class="pi pi-envelope text-xs" />
                  <span class="truncate max-w-[12rem]">{{ data.email }}</span>
                </a>
                <span v-else class="text-xs text-gray-400">—</span>
              </template>
            </Column>

            <!-- Colaborador -->
            <Column header="Colaborador" style="width: 7rem; text-align: center">
              <template #body="{ data }">
                <Tag
                  :value="data.colaborador ? 'Sim' : 'Não'"
                  :severity="data.colaborador ? 'success' : 'secondary'"
                  class="text-xs"
                />
              </template>
            </Column>

            <!-- Departamentos Vinculados -->
            <Column header="Deptos." style="width: 8rem; text-align: center">
              <template #body="{ data }">
                <Tag
                  :value="`${data.departamentos_count} depto(s)`"
                  :severity="data.departamentos_count > 0 ? 'warn' : 'secondary'"
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
                    title="Editar Curso"
                    @click="openEditModal(data)"
                  />
                  <Button
                    icon="pi pi-trash"
                    :severity="data.departamentos_count > 0 ? 'secondary' : 'danger'"
                    text
                    rounded
                    :disabled="data.departamentos_count > 0"
                    :title="
                      data.departamentos_count > 0
                        ? 'Bloqueado: Existem departamentos/laboratórios vinculados a este curso'
                        : 'Excluir Curso'
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

    <!-- MODAL DE CRIAÇÃO / EDIÇÃO DE CURSO -->
    <Dialog
      v-model:visible="dialogVisible"
      modal
      :header="editingCurso ? 'Editar Curso' : 'Novo Curso'"
      :style="{ width: '90vw', maxWidth: '520px' }"
    >
      <form @submit.prevent="submitForm" class="space-y-4 pt-2">
        <!-- Nome do Curso -->
        <div>
          <label
            for="curso_nome"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"
          >
            Nome do Curso <span class="text-red-500">*</span>
          </label>
          <InputText
            id="curso_nome"
            v-model="form.nome"
            placeholder="Ex: Farmácia"
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

        <!-- Campus -->
        <div>
          <label
            for="curso_campus"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"
          >
            Campus <span class="text-red-500">*</span>
          </label>
          <Select
            id="curso_campus"
            v-model="form.campus_id"
            :options="campuses"
            optionLabel="nome"
            optionValue="id"
            placeholder="Selecione o campus pertencente"
            class="w-full"
            :invalid="Boolean(form.errors.campus_id)"
          />
          <Message
            v-if="form.errors.campus_id"
            severity="error"
            size="small"
            variant="simple"
            class="mt-1"
          >
            {{ form.errors.campus_id }}
          </Message>
        </div>

        <!-- Disciplina de Referência -->
        <div>
          <label
            for="curso_disciplina"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"
          >
            Disciplina de Referência (Limite PINC)
          </label>
          <Select
            id="curso_disciplina"
            v-model="form.disciplina_id"
            :options="disciplinas"
            optionLabel="nome"
            optionValue="id"
            placeholder="Selecione a disciplina (opcional)"
            showClear
            class="w-full"
            :invalid="Boolean(form.errors.disciplina_id)"
          />
          <small class="text-xs text-gray-500 dark:text-gray-400 mt-1 block">
            Define o nível máximo de PINC suportado pelo curso (ex: PINC 4).
          </small>
          <Message
            v-if="form.errors.disciplina_id"
            severity="error"
            size="small"
            variant="simple"
            class="mt-1"
          >
            {{ form.errors.disciplina_id }}
          </Message>
        </div>

        <!-- E-mail Institucional -->
        <div>
          <label
            for="curso_email"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"
          >
            E-mail Institucional de Contato
          </label>
          <InputText
            id="curso_email"
            v-model="form.email"
            type="email"
            placeholder="Ex: curso@ufrj.br"
            class="w-full"
            :invalid="Boolean(form.errors.email)"
          />
          <Message
            v-if="form.errors.email"
            severity="error"
            size="small"
            variant="simple"
            class="mt-1"
          >
            {{ form.errors.email }}
          </Message>
        </div>

        <!-- Flag Colaborador -->
        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
          <div>
            <span class="text-sm font-medium text-gray-800 dark:text-gray-200 block">
              Permite Colaborador Externo
            </span>
            <span class="text-xs text-gray-500 dark:text-gray-400">
              Habilita indicação de colaboradores e participantes externos.
            </span>
          </div>
          <ToggleSwitch v-model="form.colaborador" />
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
            :label="editingCurso ? 'Salvar Alterações' : 'Cadastrar Curso'"
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
            Tem certeza que deseja excluir o curso
            <strong>{{ cursoToDelete?.nome }}</strong
            >?
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Esta ação removerá o curso da lista ativa do sistema.
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