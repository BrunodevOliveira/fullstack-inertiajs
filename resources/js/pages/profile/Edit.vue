<script setup>
import { useForm } from '@inertiajs/vue3';
import AppHeader from '@/components/AppHeader.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Message from 'primevue/message';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    nome_social: props.user.nome_social || '',
    telefone: props.user.telefone || '',
    lattes: props.user.lattes || '',
});

const submit = () => {
    form.put('/meu-perfil', {
        preserveScroll: true,
    });
};
</script>

<template>
    <div>
        <AppHeader title="Meu Perfil" />

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Cabeçalho da Página -->
            <div class="pb-4 border-b border-gray-200 dark:border-gray-700">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                    Meu Perfil
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Consulte seus dados acadêmicos e gerencie suas informações de contato.
                </p>
            </div>

            <!-- CARD 1: Dados Institucionais / Acadêmicos (Somente Leitura) -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-xs border border-gray-200 dark:border-gray-700 space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-gray-100 dark:border-gray-700">
                    <i class="pi pi-id-card text-emerald-600 text-lg" />
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white">
                        Dados Institucionais (Somente Leitura)
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                            Nome Completo
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700">
                            {{ user.nome }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                            CPF
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700 font-mono">
                            {{ user.cpf }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                            E-mail Institucional
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700">
                            {{ user.email }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                            Perfis Ativos
                        </label>
                        <div class="flex flex-wrap gap-1.5 p-2 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700 min-h-[42px] items-center">
                            <Tag
                                v-for="perfil in user.perfis"
                                :key="perfil.id"
                                :value="perfil.nome"
                                severity="info"
                                class="!text-xs"
                            />
                        </div>
                    </div>

                    <!-- Dados específicos de Docente / Técnico (SIAPE) -->
                    <div v-if="user.siape">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                            Matrícula SIAPE
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700 font-mono">
                            {{ user.siape }}
                        </p>
                    </div>

                    <!-- Dados específicos de Aluno (SIRA e Curso) -->
                    <div v-if="user.sira">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                            Matrícula SIRA
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700 font-mono">
                            {{ user.sira }}
                        </p>
                    </div>

                    <div v-if="user.aluno?.curso_importado" class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                            Curso Vinculado
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700">
                            {{ user.aluno.curso_importado }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- CARD 2: Informações Pessoais e Currículo (Formulário Editável) -->
            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-xs border border-gray-200 dark:border-gray-700 space-y-5">
                <div class="flex items-center gap-2 pb-3 border-b border-gray-100 dark:border-gray-700">
                    <i class="pi pi-user-edit text-emerald-600 text-lg" />
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white">
                        Informações Editáveis
                    </h2>
                </div>

                <div class="space-y-4">
                    <!-- Nome Social -->
                    <div>
                        <label for="nome_social" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nome Social <span class="text-xs text-gray-400">(Opcional)</span>
                        </label>
                        <InputText
                            id="nome_social"
                            v-model="form.nome_social"
                            class="w-full"
                            placeholder="Nome social pelo qual prefere ser chamado(a)"
                            :invalid="!!form.errors.nome_social"
                        />
                        <Message v-if="form.errors.nome_social" severity="error" size="small" variant="simple" class="mt-1">
                            {{ form.errors.nome_social }}
                        </Message>
                    </div>

                    <!-- Telefone / WhatsApp -->
                    <div>
                        <label for="telefone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Telefone / WhatsApp
                        </label>
                        <InputText
                            id="telefone"
                            v-model="form.telefone"
                            class="w-full"
                            placeholder="(00) 00000-0000"
                            :invalid="!!form.errors.telefone"
                        />
                        <Message v-if="form.errors.telefone" severity="error" size="small" variant="simple" class="mt-1">
                            {{ form.errors.telefone }}
                        </Message>
                    </div>

                    <!-- Link do Currículo Lattes -->
                    <div>
                        <label for="lattes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Link do Currículo Lattes
                        </label>
                        <div class="flex gap-2">
                            <InputText
                                id="lattes"
                                v-model="form.lattes"
                                class="w-full"
                                placeholder="http://lattes.cnpq.br/1234567890123456"
                                :invalid="!!form.errors.lattes"
                            />
                            <a
                                v-if="form.lattes"
                                :href="form.lattes"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center justify-center px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg text-gray-700 dark:text-gray-200 text-sm transition-colors"
                                title="Abrir link em nova aba"
                            >
                                <i class="pi pi-external-link" />
                            </a>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Obrigatório para coordenadores de projetos e bolsistas de pesquisa.
                        </p>
                        <Message v-if="form.errors.lattes" severity="error" size="small" variant="simple" class="mt-1">
                            {{ form.errors.lattes }}
                        </Message>
                    </div>
                </div>

                <!-- Botão de Salvar -->
                <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700">
                    <Button
                        type="submit"
                        label="Salvar Alterações"
                        icon="pi pi-check"
                        :loading="form.processing"
                    />
                </div>
            </form>
        </div>
    </div>
</template>