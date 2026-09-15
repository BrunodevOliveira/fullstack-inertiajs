<script setup>
import { ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Drawer from 'primevue/drawer';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

// Inicialização do serviço de Toast do PrimeVue
const toast = useToast();

// Controle de visibilidade da sidebar no mobile
const mobileSidebarOpen = ref(false);

// Itens de navegação do sistema PINC
const navItems = [
    { label: 'Início', href: '/', icon: 'pi pi-home' },
    { label: 'Projetos', href: '/projetos', icon: 'pi pi-folder' },
    { label: 'Meus Projetos', href: '/meus-projetos', icon: 'pi pi-bookmark' },
    { label: 'Usuários', href: '/usuarios', icon: 'pi pi-users' },
    { label: 'Relatórios & Avaliações', href: '/avaliacoes', icon: 'pi pi-file-edit' },
];

const page = usePage();

// Watcher para disparar Toasts automaticamente quando o backend enviar flash messages
watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) {
            return;
        }

        if (flash.success) {
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: flash.success,
                life: 4000,
            });
        }
        if (flash.error) {
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: flash.error,
                life: 5000,
            });
        }
        if (flash.info) {
            toast.add({
                severity: 'info',
                summary: 'Informação',
                detail: flash.info,
                life: 4000,
            });
        }
        if (flash.warn) {
            toast.add({
                severity: 'warn',
                summary: 'Atenção',
                detail: flash.warn,
                life: 4500,
            });
        }
    },
    { deep: true, immediate: true }
);

// Função auxiliar para verificar se o link atual está ativo
const isActive = (href) => {
    if (href === '/') {
        return page.url === '/';
    }
    return page.url.startsWith(href);
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col">
        <!-- Notificações Toast Globais -->
        <Toast position="top-right" />
        <!-- TOPBAR / CABEÇALHO SUPERIOR -->
        <header class="h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30 shadow-xs">
            <!-- Lado Esquerdo: Botão Mobile + Logo -->
            <div class="flex items-center gap-3">
                <!-- Botão Hamburger (Apenas no Mobile) -->
                <Button
                    icon="pi pi-bars"
                    severity="secondary"
                    variant="text"
                    rounded
                    class="md:hidden"
                    aria-label="Abrir Menu"
                    @click="mobileSidebarOpen = true"
                />

                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black tracking-tight text-emerald-600 dark:text-emerald-400">PINC</span>
                    <span class="hidden sm:inline text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        Iniciação Científica
                    </span>
                </div>
            </div>

            <!-- Lado Direito: Informações do Usuário e Ações -->
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-100">Usuário Convidado</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Docente / Pesquisador</p>
                </div>
                
                <Button
                    icon="pi pi-user"
                    severity="secondary"
                    rounded
                    outlined
                    aria-label="Perfil"
                />
            </div>
        </header>

        <!-- CORPO DA APLICAÇÃO (SIDEBAR + CONTEÚDO) -->
        <div class="flex flex-1">
            <!-- SIDEBAR DESKTOP (Fixa em telas md+) -->
            <aside class="hidden md:flex flex-col w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 p-4">
                <nav class="space-y-1">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                            isActive(item.href)
                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 font-semibold'
                                : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50'
                        ]"
                    >
                        <i :class="[item.icon, 'text-base']" />
                        <span>{{ item.label }}</span>
                    </Link>
                </nav>
            </aside>

            <!-- SIDEBAR MOBILE (Drawer do PrimeVue) -->
            <Drawer v-model:visible="mobileSidebarOpen" header="Menu de Navegação" class="!w-72">
                <nav class="space-y-1 mt-2">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                            isActive(item.href)
                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 font-semibold'
                                : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50'
                        ]"
                        @click="mobileSidebarOpen = false"
                    >
                        <i :class="[item.icon, 'text-base']" />
                        <span>{{ item.label }}</span>
                    </Link>
                </nav>
            </Drawer>

            <!-- ÁREA PRINCIPAL: ONDE AS PÁGINAS SERÃO RENDERIZADAS PELO SLOT -->
            <main class="flex-1 p-6 md:p-8 max-w-7xl mx-auto w-full">
                <!-- Ponto de injeção das páginas do Inertia -->
                <slot />
            </main>
        </div>
    </div>
</template>
