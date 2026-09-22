<script setup>
import { ref, reactive } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import AppHeader from '@/components/AppHeader.vue';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import MultiSelect from 'primevue/multiselect';
import ToggleSwitch from 'primevue/toggleswitch';
import Message from 'primevue/message';

const props = defineProps({
    usuarios: {
        type: Object,
        required: true,
    },
    perfis: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ busca: '', perfil_id: '', situacao: '' }),
    },
    can_impersonate: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();

// Estado dos Filtros
const filters = reactive({
    busca: props.filters.busca || '',
    perfil_id: props.filters.perfil_id ? Number(props.filters.perfil_id) : null,
    situacao: props.filters.situacao !== '' ? props.filters.situacao : null,
});

// Opções para o filtro de situação
const situacaoOptions = [
    { label: 'Todos os status', value: null },
    { label: 'Ativos', value: '1' },
    { label: 'Inativos', value: '0' },
];

// Opções de perfis para o filtro
const perfilFilterOptions = [
    { label: 'Todos os perfis', value: null },
    ...props.perfis.map(p => ({ label: p.nome, value: p.id })),
];

// Dispara a busca e filtros
const applyFilters = () => {
    router.get('/usuarios', {
        busca: filters.busca || undefined,
        perfil_id: filters.perfil_id || undefined,
        situacao: filters.situacao !== null ? filters.situacao : undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    filters.busca = '';
    filters.perfil_id = null;
    filters.situacao = null;
    applyFilters();
};

// Paginação da tabela
const onPage = (event) => {
    router.get('/usuarios', {
        page: event.page + 1,
        busca: filters.busca || undefined,
        perfil_id: filters.perfil_id || undefined,
        situacao: filters.situacao !== null ? filters.situacao : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Controle do Modal de Edição
const dialogVisible = ref(false);
const editingUser = ref(null);

const form = useForm({
    perfis: [],
    situacao: true,
});

const openEditModal = (user) => {
    editingUser.value = user;
    form.perfis = user.perfis.map(p => p.id);
    form.situacao = Boolean(user.situacao);
    form.clearErrors();
    dialogVisible.value = true;
};

const saveUser = () => {
    if (!editingUser.value) return;

    form.put(`/usuarios/${editingUser.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            dialogVisible.value = false;
        },
    });
};

// Ação de Impersonar direto da tabela
const impersonate = (userId) => {
    router.post(`/impersonar/${userId}`);
};

// Severidade de cores para os perfis
const getPerfilSeverity = (perfilId) => {
    const severities = {
        1: 'danger',    // Root
        2: 'warn',      // Admin
        3: 'info',      // Docente
        4: 'success',   // Discente
        5: 'secondary', // Tecnico
    };
    return severities[perfilId] || 'info';
};
</script>

<template>
    <div>
        <AppHeader title="Gestão de Usuários" />

        <div class="space-y-6">
            <!-- Cabeçalho -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                        Gestão de Usuários
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Controle de contas, atribuição de perfis e permissões de acesso ao sistema.
                    </p>
                </div>
            </div>

            <!-- Card com Filtros e Tabela -->
            <Card class="!bg-white dark:!bg-gray-800 !border !border-gray-200 dark:!border-gray-700 rounded-xl shadow-xs">
                <template #content>
                    <!-- Barra de Filtros -->
                    <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between mb-6">
                        <div class="flex flex-1 flex-col sm:flex-row gap-3">
                            <!-- Busca por Nome / CPF -->
                            <div class="relative flex-1">
                                <InputText
                                    v-model="filters.busca"
                                    placeholder="Buscar por nome, CPF ou e-mail..."
                                    class="w-full"
                                    @keydown.enter="applyFilters"
                                />
                            </div>

                            <!-- Filtro por Perfil -->
                            <Select
                                v-model="filters.perfil_id"
                                :options="perfilFilterOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Filtrar por Perfil"
                                class="w-full sm:w-56"
                                @change="applyFilters"
                            />

                            <!-- Filtro por Situação -->
                            <Select
                                v-model="filters.situacao"
                                :options="situacaoOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Situação"
                                class="w-full sm:w-44"
                                @change="applyFilters"
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
                                v-if="filters.busca || filters.perfil_id || filters.situacao !== null"
                                icon="pi pi-filter-slash"
                                severity="secondary"
                                text
                                title="Limpar Filtros"
                                @click="clearFilters"
                            />
                        </div>
                    </div>

                    <!-- Tabela de Usuários -->
                    <DataTable
                        :value="usuarios.data"
                        lazy
                        paginator
                        :rows="usuarios.per_page"
                        :totalRecords="usuarios.total"
                        :first="(usuarios.current_page - 1) * usuarios.per_page"
                        tableStyle="min-width: 50rem"
                        @page="onPage"
                        
                    >
                        <template #empty>
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <i class="pi pi-users text-4xl mb-3 opacity-40 block" />
                                Nenhum usuário encontrado com os filtros aplicados.
                            </div>
                        </template>

                        <!-- Nome e Identificação -->
                        <Column header="Usuário / Identificação">
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-gray-900 dark:text-gray-100 text-sm">
                                        {{ data.nome }}
                                    </span>
                                    <span v-if="data.nome_social" class="text-xs text-emerald-600 dark:text-emerald-400">
                                        Nome Social: {{ data.nome_social }}
                                    </span>
                                    <span class="text-xs text-gray-400 font-mono mt-0.5">
                                        CPF: {{ data.cpf }}
                                    </span>
                                </div>
                            </template>
                        </Column>

                        <!-- E-mail / Contato -->
                        <Column field="email" header="E-mail" />

                        <!-- Perfis Atribuídos -->
                        <Column header="Perfis">
                            <template #body="{ data }">
                                <div class="flex flex-wrap gap-1">
                                    <Tag
                                        v-for="perfil in data.perfis"
                                        :key="perfil.id"
                                        :value="perfil.nome"
                                        :severity="getPerfilSeverity(perfil.id)"
                                        class="!text-xs"
                                    />
                                </div>
                            </template>
                        </Column>

                        <!-- Situação -->
                        <Column header="Status" style="width: 110px">
                            <template #body="{ data }">
                                <Tag
                                    :value="data.situacao ? 'Ativo' : 'Inativo'"
                                    :severity="data.situacao ? 'success' : 'danger'"
                                    class="!text-xs font-semibold"
                                />
                            </template>
                        </Column>

                        <!-- Ações -->
                        <Column header="Ações" style="width: 140px; text-align: right">
                            <template #body="{ data }">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Botão de Personificação (Apenas para Root) -->
                                    <Button
                                        v-if="can_impersonate && page.props.auth?.user?.id !== data.id"
                                        icon="pi pi-user-edit"
                                        severity="warn"
                                        text
                                        rounded
                                        size="small"
                                        title="Personificar este usuário"
                                        @click="impersonate(data.id)"
                                    />

                                    <!-- Botão de Edição -->
                                    <Button
                                        icon="pi pi-pencil"
                                        severity="secondary"
                                        text
                                        rounded
                                        size="small"
                                        title="Editar Perfis e Status"
                                        @click="openEditModal(data)"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>

        <!-- MODAL DE EDIÇÃO DE PERFIS E STATUS -->
        <Dialog
            v-model:visible="dialogVisible"
            modal
            :header="`Editar Usuário: ${editingUser?.nome || ''}`"
            :style="{ width: '32rem' }"
        >
            <form @submit.prevent="saveUser" class="space-y-5 pt-2">
                <!-- Perfis -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                        Perfis do Usuário
                    </label>
                    <MultiSelect
                        v-model="form.perfis"
                        :options="perfis"
                        optionLabel="nome"
                        optionValue="id"
                        placeholder="Selecione os perfis"
                        class="w-full"
                        display="chip"
                        :invalid="!!form.errors.perfis"
                    />
                    <Message v-if="form.errors.perfis" severity="error" size="small" variant="simple" class="mt-1">
                        {{ form.errors.perfis }}
                    </Message>
                </div>

                <!-- Status Ativo / Inativo -->
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <div>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200 block">
                            Conta Ativa
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Usuários inativos não conseguem fazer login no sistema.
                        </span>
                    </div>
                    <ToggleSwitch v-model="form.situacao" />
                </div>

                <!-- Ações do Modal -->
                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <Button
                        label="Cancelar"
                        severity="secondary"
                        text
                        @click="dialogVisible = false"
                    />
                    <Button
                        type="submit"
                        label="Salvar Alterações"
                        icon="pi pi-check"
                        :loading="form.processing"
                    />
                </div>
            </form>
        </Dialog>
    </div>
</template>