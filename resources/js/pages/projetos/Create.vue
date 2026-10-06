<script setup>
import AppHeader from "@/components/AppHeader.vue";
import ProjetoForm from "./partials/ProjetoForm.vue";

defineProps({
  cursos: { type: Array, required: true },
  departamentos: { type: Array, required: true },
  agencias: { type: Array, required: true },
});
</script>

<template>
  <div>
    <AppHeader title="Novo Projeto de Pesquisa" />

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          Cadastrar Novo Projeto
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          Preencha os dados institucionais e acadêmicos da sua proposta de pesquisa.
        </p>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <ProjetoForm
          :cursos="cursos"
          :departamentos="departamentos"
          :agencias="agencias"
        />
      </div>
    </div>
  </div>
</template>