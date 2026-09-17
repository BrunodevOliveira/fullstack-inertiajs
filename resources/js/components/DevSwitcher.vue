<script setup>
import { ref, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import Button from "primevue/button";
import Tag from "primevue/tag";

const page = usePage();
const isOpen = ref(false);
const loadingCpf = ref(null);
const impersonatingCpf = ref(null);

// Lista dos usuários de demonstração criados no UsuarioSeeder
const devUsers = [
  {
    id: 1,
    cpf: "00000000000",
    nome: "Root (Super Admin)",
    perfil: "Root",
    severity: "danger",
  },
  {
    id: 2,
    cpf: "11111111111",
    nome: "Coordenação",
    perfil: "Administrador",
    severity: "warn",
  },
  {
    id: 3,
    cpf: "22222222222",
    nome: "Prof. Dr. Docente",
    perfil: "Docente",
    severity: "info",
  },
  {
    id: 4,
    cpf: "33333333333",
    nome: "Discente Aluno",
    perfil: "Discente",
    severity: "success",
  },
  {
    id: 5,
    cpf: "44444444444",
    nome: "Técnico Administrativo",
    perfil: "Técnico",
    severity: "secondary",
  },
  {
    id: 6,
    cpf: "55555555555",
    nome: "Docente + Admin",
    perfil: "Docente / Admin",
    severity: "contrast",
  },
];

// Verifica se o usuário atual é Root
const isRoot = computed(() => {
  return page.props.auth?.user?.perfis?.some((p) => p.id === 1);
});

// Verifica se está personificando
const isImpersonating = computed(() => {
  return !!page.props.impersonator;
});

const loginAs = (cpf) => {
  loadingCpf.value = cpf;
  router.post(
    "/dev/login",
    { cpf },
    {
      onFinish: () => {
        loadingCpf.value = null;
        isOpen.value = false;
      },
    }
  );
};

const impersonate = (id, cpf) => {
  impersonatingCpf.value = cpf;
  router.post(
    `/impersonar/${id}`,
    {},
    {
      onFinish: () => {
        impersonatingCpf.value = null;
        isOpen.value = false;
      },
    }
  );
};
</script>

<template>
  <!-- Exibe apenas em ambiente local -->
  <div
    v-if="page.props.is_local"
    class="fixed bottom-4 right-4 z-50 flex flex-col items-end"
  >
    <!-- Painel Expandido com as Opções de Usuários -->
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform scale-95 opacity-0 translate-y-2"
      enter-to-class="transform scale-100 opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="transform scale-100 opacity-100 translate-y-0"
      leave-to-class="transform scale-95 opacity-0 translate-y-2"
    >
      <div
        v-if="isOpen"
        class="mb-3 w-88 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 p-4"
      >
        <div
          class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700"
        >
          <div class="flex items-center gap-2">
            <i class="pi pi-bolt text-amber-500 font-bold" />
            <span
              class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200"
            >
              Dev Switcher
            </span>
          </div>
          <Button
            icon="pi pi-times"
            variant="text"
            severity="secondary"
            rounded
            size="small"
            aria-label="Fechar"
            @click="isOpen = false"
          />
        </div>

        <div class="mt-3 space-y-2 max-h-80 overflow-y-auto pr-1">
          <div
            v-for="user in devUsers"
            :key="user.cpf"
            class="p-2.5 rounded-lg border border-gray-100 dark:border-gray-700 hover:border-emerald-500 dark:hover:border-emerald-500 hover:bg-emerald-50/30 dark:hover:bg-emerald-950/20 transition-all flex items-center justify-between gap-2"
          >
            <!-- Lado Esquerdo: Botão de Login Direto -->
            <button
              type="button"
              :disabled="loadingCpf !== null || impersonatingCpf !== null"
              class="flex flex-col text-left flex-1 cursor-pointer disabled:opacity-50"
              @click="loginAs(user.cpf)"
              title="Entrar com este perfil"
            >
              <span
                class="text-xs font-semibold text-gray-800 dark:text-gray-100 hover:text-emerald-600"
              >
                {{ user.nome }}
              </span>
              <span class="text-[10px] text-gray-400 font-mono">
                CPF: {{ user.cpf }}
              </span>
            </button>

            <!-- Lado Direito: Tag e Ações -->
            <div class="flex items-center gap-1.5">
              <Tag :value="user.perfil" :severity="user.severity" class="!text-[10px]" />

              <!-- Botão de Personificação (Apenas visível se estiver logado como Root e não for a própria conta) -->
              <Button
                v-if="isRoot && !isImpersonating && page.props.auth?.user?.id !== user.id"
                icon="pi pi-user-edit"
                severity="warn"
                size="small"
                text
                rounded
                v-tooltip.top="'Personificar este usuário'"
                :loading="impersonatingCpf === user.cpf"
                @click="impersonate(user.id, user.cpf)"
              />
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Botão Flutuante Gatilho -->
    <Button
      :icon="isOpen ? 'pi pi-times' : 'pi pi-bolt'"
      :severity="isOpen ? 'secondary' : 'warn'"
      rounded
      raised
      class="!w-12 !h-12 shadow-lg"
      :aria-label="isOpen ? 'Fechar Dev Switcher' : 'Abrir Dev Switcher'"
      @click="isOpen = !isOpen"
    />
  </div>
</template>
