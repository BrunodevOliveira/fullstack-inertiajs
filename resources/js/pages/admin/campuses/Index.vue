<script setup>
import { ref, reactive } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AppHeader from "@/components/AppHeader.vue";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Tag from "primevue/tag";
import Dialog from "primevue/dialog";
import Message from "primevue/message";

const props = defineProps({
  campuses: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ busca: "" }),
  },
});

// Estado reativo dos filtros de busca
const filters = reactive({
  busca: props.filters.busca || "",
});

// Dispara a busca textual preservando o estado
const applyFilters = () => {
  router.get(
    "/campuses",
    {
      busca: filters.busca || undefined,
    },
    {
      preserveState: true, // Impede que a tela seja recriada do zero, mantendo o foco e o estado dos componentes.
      replace: true, //Atualiza a URL sem empilhar um novo histórico no botão "Voltar" do navegador para cada letra buscada.
    }
  );
};

const clearFilters = () => {
  filters.busca = "";
  applyFilters();
};

// Paginação remota sincronizada com o backend
const onPage = (event) => {
  router.get(
    "/campuses",
    {
      page: event.page + 1,
      busca: filters.busca || undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
};

// Controle do Modal de Criação / Edição
const dialogVisible = ref(false);
const editingCampus = ref(null);

const form = useForm({
  nome: "",
});

// Abre modal para NOVO campus
const openCreateModal = () => {
  editingCampus.value = null;
  form.reset();
  form.clearErrors();
  dialogVisible.value = true;
};

// Abre modal para EDITAR campus existente
const openEditModal = (campus) => {
  editingCampus.value = campus;
  form.nome = campus.nome;
  form.clearErrors();
  dialogVisible.value = true;
};

// Submissão do formulário (POST para novo / PUT para edição)
const submitForm = () => {
  if (editingCampus.value) {
    form.put(`/campuses/${editingCampus.value.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        dialogVisible.value = false;
      },
    });
  } else {
    form.post("/campuses", {
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
const campusToDelete = ref(null);
const isDeleting = ref(false);

const confirmDelete = (campus) => {
  campusToDelete.value = campus;
  deleteDialogVisible.value = true;
};

const executeDelete = () => {
  if (!campusToDelete.value) return;

  isDeleting.value = true;
  router.delete(`/campuses/${campusToDelete.value.id}`, {
    preserveScroll: true,
    onFinish: () => {
      isDeleting.value = false;
      deleteDialogVisible.value = false;
      campusToDelete.value = null;
    },
  });
};
</script>

<template>
  <div>
    <AppHeader title="Gestão de Campi" />

    <div class="space-y-6">
      <!-- Cabeçalho da Página -->
      <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-700"
      >
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
            Gestão de Campi
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Cadastre e gerencie as unidades e polos acadêmicos da instituição.
          </p>
        </div>
        <div>
          <Button
            label="Novo Campus"
            icon="pi pi-plus"
            severity="primary"
            class="shadow-xs"
            @click="openCreateModal"
          />
        </div>
      </div>

      <!-- Card Principal com Busca e Tabela -->
      <Card
        class="!bg-white dark:!bg-gray-800 !border !border-gray-200 dark:!border-gray-700 rounded-xl shadow-xs"
      >
        <template #content>
          <!-- Barra de Busca -->
          <div
            class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between mb-6"
          >
            <div class="relative flex-1 max-w-md">
              <InputText
                v-model="filters.busca"
                placeholder="Buscar por nome do campus..."
                class="w-full"
                @keydown.enter="applyFilters"
              />
            </div>
            <div class="flex gap-2">
              <Button
                icon="pi pi-search"
                label="Buscar"
                severity="primary"
                @click="applyFilters"
              />
              <Button
                v-if="filters.busca"
                icon="pi pi-filter-slash"
                severity="secondary"
                text
                title="Limpar Busca"
                @click="clearFilters"
              />
            </div>
          </div>

          <!-- Tabela de Campi -->
          <DataTable
            :value="campuses.data"
            lazy
            paginator
            :rows="campuses.per_page"
            :totalRecords="campuses.total"
            :first="(campuses.current_page - 1) * campuses.per_page"
            tableStyle="min-width: 40rem"
            @page="onPage"
          >
            <template #empty>
              <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <i class="pi pi-building text-4xl mb-3 opacity-40 block" />
                Nenhum campus encontrado.
              </div>
            </template>

            <!-- ID -->
            <Column field="id" header="#" style="width: 5rem">
              <template #body="{ data }">
                <span class="font-mono text-xs text-gray-400">{{ data.id }}</span>
              </template>
            </Column>

            <!-- Nome do Campus -->
            <Column header="Nome do Campus">
              <template #body="{ data }">
                <div class="flex items-center gap-2">
                  <i class="pi pi-building text-gray-400 text-sm" />
                  <span class="font-semibold text-gray-900 dark:text-gray-100 text-sm">
                    {{ data.nome }}
                  </span>
                </div>
              </template>
            </Column>

            <!-- Cursos Vinculados -->
            <Column header="Cursos Vinculados" style="width: 14rem">
              <template #body="{ data }">
                <Tag
                  :value="
                    data.cursos_count > 0
                      ? `${data.cursos_count} ${
                          data.cursos_count === 1 ? 'curso' : 'cursos'
                        }`
                      : 'Nenhum curso'
                  "
                  :severity="data.cursos_count > 0 ? 'info' : 'secondary'"
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
                    title="Editar Campus"
                    @click="openEditModal(data)"
                  />
                  <Button
                    icon="pi pi-trash"
                    :severity="data.cursos_count > 0 ? 'secondary' : 'danger'"
                    text
                    rounded
                    :disabled="data.cursos_count > 0"
                    :title="
                      data.cursos_count > 0
                        ? 'Bloqueado: Existem cursos vinculados a este campus'
                        : 'Excluir Campus'
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

    <!-- MODAL DE CRIAÇÃO / EDIÇÃO DE CAMPUS -->
    <Dialog
      v-model:visible="dialogVisible"
      modal
      :header="editingCampus ? 'Editar Campus' : 'Novo Campus'"
      :style="{ width: '90vw', maxWidth: '480px' }"
    >
      <form @submit.prevent="submitForm" class="space-y-4 pt-2">
        <div>
          <label
            for="campus_nome"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"
          >
            Nome do Campus <span class="text-red-500">*</span>
          </label>
          <InputText
            id="campus_nome"
            v-model="form.nome"
            placeholder="Ex: Cidade Universitária (Fundão)"
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
            :label="editingCampus ? 'Salvar Alterações' : 'Cadastrar Campus'"
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
            Tem certeza que deseja excluir o campus
            <strong>{{ campusToDelete?.nome }}</strong
            >?
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Esta ação removerá o campus da lista ativa do sistema.
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
