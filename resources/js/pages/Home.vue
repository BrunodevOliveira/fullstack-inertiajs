

<script setup>
import { router } from '@inertiajs/vue3';
import AppHeader from '../components/AppHeader.vue';
import Button from 'primevue/button';

defineProps({
    title: {
        type: String,
        default: 'Home'
    }
});

// Dispara uma requisição ao backend para testar as Flash Messages
const triggerToast = (type) => {
    router.post('/teste-flash', { type }, {
        preserveScroll: true,
    });
};
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

            <!-- Card de Demonstração do Sistema de Notificações Toast (E1-T5) -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-xs border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">
                    🔔 Teste do Sistema Global de Notificações (Toast)
                </h2>
                <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">
                    Clique nos botões abaixo para enviar uma requisição ao Laravel via Inertia. O backend responde com uma <strong>Flash Message</strong> de sessão, e o <code>AppLayout.vue</code> captura automaticamente para exibir o Toast do PrimeVue:
                </p>
                
                <div class="flex flex-wrap gap-3">
                    <Button label="Toast Sucesso" icon="pi pi-check-circle" severity="success" @click="triggerToast('success')" />
                    <Button label="Toast Erro" icon="pi pi-times-circle" severity="danger" @click="triggerToast('error')" />
                    <Button label="Toast Atenção" icon="pi pi-exclamation-triangle" severity="warn" @click="triggerToast('warn')" />
                    <Button label="Toast Info" icon="pi pi-info-circle" severity="info" @click="triggerToast('info')" />
                </div>
            </div>
        </div>
    </div>
</template>
