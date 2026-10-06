<script setup>
import { computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import InputNumber from "primevue/inputnumber";
import Select from "primevue/select";
import Button from "primevue/button";
import Message from "primevue/message";

const props = defineProps({
  projeto: {
    type: Object,
    default: null,
  },
  cursos: { type: Array, required: true },
  departamentos: { type: Array, required: true },
  agencias: { type: Array, required: true },
});

const isEdit = computed(() => !!props.projeto);

const form = useForm({
  titulo: props.projeto?.titulo || "",
  assunto: props.projeto?.assunto || "",
  descricao: props.projeto?.descricao || "",
  vagas: props.projeto?.vagas || 1,
  curso_id: props.projeto?.departamento?.curso_id || null,
  departamento_id: props.projeto?.departamento_id || null,
  agencia_id: props.projeto?.agencia_id || null,
});

const availableDepartamentos = computed(() => {
  if (!form.curso_id) return [];
  return props.departamentos.filter((d) => d.curso_id === form.curso_id);
});

const onCursoChange = () => {
  form.departamento_id = null;
};

const submit = () => {
  if (isEdit.value) {
    form.put(`/projetos/${props.projeto.id}`);
  } else {
    form.post("/projetos");
  }
};
</script>

<template>
  <form @submit.prevent="submit" class="space-y-6">
    <!-- Título -->
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        Título do Projeto *
      </label>
      <InputText
        v-model="form.titulo"
        class="w-full"
        placeholder="Ex: Análise de Algoritmos Distribuídos aplicados à IoT"
        :invalid="!!form.errors.titulo"
      />
      <Message
        v-if="form.errors.titulo"
        severity="error"
        size="small"
        variant="simple"
        class="mt-1"
      >
        {{ form.errors.titulo }}
      </Message>
    </div>

    <!-- Assunto -->
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        Assunto / Área Temática *
      </label>
      <InputText
        v-model="form.assunto"
        class="w-full"
        placeholder="Ex: Redes de Sensores Sem Fio"
        :invalid="!!form.errors.assunto"
      />
      <Message
        v-if="form.errors.assunto"
        severity="error"
        size="small"
        variant="simple"
        class="mt-1"
      >
        {{ form.errors.assunto }}
      </Message>
    </div>

    <!-- Dropdowns Encadeados: Curso e Departamento -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Curso *
        </label>
        <Select
          v-model="form.curso_id"
          :options="cursos"
          optionLabel="nome"
          optionValue="id"
          placeholder="Selecione o Curso primeiro"
          class="w-full"
          @change="onCursoChange"
        />
        <p class="text-xs text-gray-400 mt-1">Filtra os departamentos vinculados.</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Departamento / Laboratório *
        </label>
        <Select
          v-model="form.departamento_id"
          :options="availableDepartamentos"
          optionLabel="nome"
          optionValue="id"
          :placeholder="
            form.curso_id ? 'Selecione o Departamento' : 'Aguardando seleção do Curso...'
          "
          :disabled="!form.curso_id"
          class="w-full"
          :invalid="!!form.errors.departamento_id"
        />
        <Message
          v-if="form.errors.departamento_id"
          severity="error"
          size="small"
          variant="simple"
          class="mt-1"
        >
          {{ form.errors.departamento_id }}
        </Message>
      </div>
    </div>

    <!-- Vagas e Agência de Fomento -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Número de Vagas *
        </label>
        <InputNumber
          v-model="form.vagas"
          :min="1"
          showButtons
          class="w-full"
          :invalid="!!form.errors.vagas"
        />
        <Message
          v-if="form.errors.vagas"
          severity="error"
          size="small"
          variant="simple"
          class="mt-1"
        >
          {{ form.errors.vagas }}
        </Message>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Agência de Fomento (Opcional)
        </label>
        <Select
          v-model="form.agencia_id"
          :options="agencias"
          optionLabel="nome"
          optionValue="id"
          placeholder="Sem bolsa / Não aplicável"
          showClear
          class="w-full"
          :invalid="!!form.errors.agencia_id"
        />
        <Message
          v-if="form.errors.agencia_id"
          severity="error"
          size="small"
          variant="simple"
          class="mt-1"
        >
          {{ form.errors.agencia_id }}
        </Message>
      </div>
    </div>

    <!-- Descrição -->
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        Descrição Detalhada do Projeto *
      </label>
      <Textarea
        v-model="form.descricao"
        rows="5"
        class="w-full"
        placeholder="Descreva a justificativa, objetivos e metodologia do projeto..."
        :invalid="!!form.errors.descricao"
      />
      <Message
        v-if="form.errors.descricao"
        severity="error"
        size="small"
        variant="simple"
        class="mt-1"
      >
        {{ form.errors.descricao }}
      </Message>
    </div>

    <!-- Botões de Ação -->
    <div
      class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700"
    >
      <Button
        type="submit"
        :label="isEdit ? 'Salvar Alterações' : 'Cadastrar Projeto'"
        icon="pi pi-check"
        :loading="form.processing"
      />
    </div>
  </form>
</template>
