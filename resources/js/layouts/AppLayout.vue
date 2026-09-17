<script setup>
import { ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Drawer from 'primevue/drawer';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import DevSwitcher from '@/components/DevSwitcher.vue';

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

// Obtém os perfis formatados em texto (ex: "Docente, Administrador")
const userProfiles = () => {
    if (!page.props.auth?.user?.perfis?.length) return 'Convidado';
    return page.props.auth.user.perfis.map(p => p.nome).join(' / ');
};

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
        <!-- BANNER DE IMPERSONATION (Visível quando o Root estiver personificando outro usuário) -->
        <div
            v-if="page.props?.impersonator"
            class="bg-amber-500 dark:bg-amber-600 text-amber-950 dark:text-white px-4 py-2.5 shadow-md flex items-center justify-between flex-wrap gap-2 sticky top-0 z-40 border-b border-amber-600/30 font-medium text-xs sm:text-sm"
        >
            <div class="flex items-center gap-2">
                <i class="pi pi-exclamation-triangle text-base sm:text-lg animate-pulse" />
                <span>
                    <strong>Modo de Personificação Ativo:</strong> Você está conectado como 
                    <u>{{ page.props.auth.user.nome }}</u> 
                    <span class="opacity-80">(Original: {{ page.props.impersonator.nome }})</span>
                </span>
            </div>
            <Link
                href="/impersonar-sair"
                method="post"
                as="button"
                class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-950 text-white dark:bg-white dark:text-amber-900 rounded-md font-semibold text-xs hover:bg-black dark:hover:bg-amber-50 transition-colors shadow-xs cursor-pointer"
            >
                <i class="pi pi-undo text-xs" />
                <span>Voltar ao Perfil Root</span>
            </Link>
        </div>
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
                <!-- Se estiver LOGADO -->
                <template v-if="page.props.auth?.user">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                            {{ page.props.auth.user.nome }}
                        </p>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                            {{ userProfiles() }}
                        </p>
                    </div>

                    <!-- Botão de Sair (Logout) -->
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="p-button p-component p-button-outlined p-button-secondary p-button-sm !rounded-lg flex items-center gap-1.5 px-3 py-1.5 text-xs text-red-600 dark:text-red-400 hover:!bg-red-50 dark:hover:!bg-red-950/40 hover:!border-red-300"
                        title="Encerrar Sessão"
                    >
                        <i class="pi pi-sign-out text-sm" />
                        <span class="hidden sm:inline">Sair</span>
                    </Link>
                </template>

                <!-- Se for VISITANTE -->
                <template v-else>
                    <Link
                        href="/login"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition-colors"
                    >
                        <i class="pi pi-sign-in text-sm" />
                        <span>Entrar</span>
                    </Link>
                </template>
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
        <!-- Dev Switcher Flutuante para desenvolvimento local -->
        <DevSwitcher />
    </div>
</template>
