

<script setup>
import AppHeader from '../components/AppHeader.vue';
import Button from 'primevue/button';

defineProps({
    title: {
        type: String,
        default: 'Home'
    }
})
</script>

<template>
    <div>
        <AppHeader :title="title" />

        <div class="space-y-6">
            <!-- Cabeçalho da Página -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                        {{ title }}
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Bem-vindo ao Portal de Iniciação Científica (PINC).
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button label="Novo Projeto" icon="pi pi-plus" />
                </div>
            </div>

            <!-- Card de Conteúdo de Exemplo -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-xs border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">
                    Layout Mestre Configurado com Sucesso!
                </h2>
                <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">
                    Este conteúdo está sendo renderizado dentro do <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-emerald-600 dark:text-emerald-400 font-mono text-xs">&lt;slot /&gt;</code> do <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-emerald-600 dark:text-emerald-400 font-mono text-xs">AppLayout.vue</code>.
                </p>
                <div class="flex gap-3">
                    <Button label="Salvar" icon="pi pi-check" />
                    <Button label="Excluir" icon="pi pi-trash" severity="danger" outlined />
                </div>
            </div>
        </div>
    </div>
</template>
