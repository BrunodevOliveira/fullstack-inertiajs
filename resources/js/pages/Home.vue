

<script setup>
import { router } from '@inertiajs/vue3';
import AppHeader from '../components/AppHeader.vue';
import { ref } from "vue";

import Button from 'primevue/button';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Tag from 'primevue/tag';
import Paginator from 'primevue/paginator';

const props = defineProps({
    title: {
        type: String,
        default: 'Home'
    },
    filters: {
        type: Object,
        required: true,
        default: () => ({ busca: "" })
    },
    projetos: {
        type: Object,
        required: true
    }
});

const busca = ref(props.filters?.busca || "")

const aplicarBusca = () => {
    router.get('/', 
        { busca: busca.value || undefined },
        { preserveState: true, replace: true }
    )
}

const limparBusca = () => {
    busca.value = ""
    aplicarBusca()
}

const onPage = event => {
    router.get("/", 
        { page: event.page + 1, busca: busca.value || undefined }, 
        { preserveState: true, preserveScroll: true }
    )
}

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
                        Catálogo de Projetos de Iniciação Científica e Tecnológica.
                    </p>
                </div>
            </div>

            <!-- Barra de Filtros e Busca -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row gap-3">
                <IconField class="w-full">
                    <InputIcon class="pi pi-search" />
                    <InputText 
                        v-model="busca" 
                        placeholder="Buscar por título, assunto ou palavras na descrição..." 
                        class="w-full"
                        @keydown.enter="aplicarBusca"
                    />
                </IconField>
                <div class="flex gap-2 shrink-0">
                    <Button label="Buscar" icon="pi pi-search" @click="aplicarBusca" />
                    <Button 
                        v-if="busca" 
                        label="Limpar" 
                        icon="pi pi-times" 
                        severity="secondary" 
                        @click="limparBusca" 
                    />
                </div>
            </div>

            <!-- Grid de Projetos -->
            <div v-if="projetos.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Card 
                    v-for="projeto in projetos.data" 
                    :key="projeto.id" 
                    class="flex flex-col h-full shadow-xs border border-gray-200 dark:border-gray-700 hover:shadow-md transition duration-200"
                >
                    <template #title>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white line-clamp-2 leading-snug">
                            {{ projeto.titulo }}
                        </h2>
                    </template>
                    <template #subtitle>
                        <span class="text-xs font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                            {{ projeto.assunto }}
                        </span>
                    </template>
                    <template #content>
                        <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3 mb-4">
                            {{ projeto.descricao }}
                        </p>
                        
                        <div class="space-y-2 pt-3 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-500 dark:text-gray-400">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-user text-gray-400"></i>
                                <span class="truncate"><strong>Orientador:</strong> {{ projeto.responsavel?.name || 'Não informado' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="pi pi-building text-gray-400"></i>
                                <span class="truncate"><strong>Depto:</strong> {{ projeto.departamento?.nome || 'Geral' }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-2">
                                <Tag :value="`${projeto.vagas} vaga(s)`" severity="info" icon="pi pi-users" />
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Empty State (Nenhum Projeto Encontrado) -->
            <div 
                v-else 
                class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8"
            >
                <i class="pi pi-folder-open text-4xl text-gray-400 mb-3 block"></i>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Nenhum projeto encontrado
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Não encontramos projetos ativos com os critérios pesquisados.
                </p>
                <Button 
                    v-if="busca" 
                    label="Limpar busca" 
                    icon="pi pi-times" 
                    severity="secondary" 
                    class="mt-4" 
                    @click="limparBusca" 
                />
            </div>

            <!-- Paginação -->
            <Paginator 
                v-if="projetos.total > projetos.per_page"
                :rows="projetos.per_page" 
                :totalRecords="projetos.total" 
                :first="(projetos.current_page - 1) * projetos.per_page" 
                @page="onPage" 
            />

            <!-- Card de Teste do Toast (E1-T5 mantido para testes) -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-xs border border-gray-200 dark:border-gray-700 mt-8">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    🔔 Teste do Sistema Global de Notificações (Toast)
                </h2>
                <div class="flex flex-wrap gap-2">
                    <Button label="Sucesso" severity="success" size="small" @click="triggerToast('success')" />
                    <Button label="Erro" severity="danger" size="small" @click="triggerToast('error')" />
                    <Button label="Atenção" severity="warn" size="small" @click="triggerToast('warn')" />
                    <Button label="Info" severity="info" size="small" @click="triggerToast('info')" />
                </div>
            </div>
        </div>
    </div>
</template>
