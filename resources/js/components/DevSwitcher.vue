<script setup>
import { ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import Button from "primevue/button";
import Tag from "primevue/tag";

const page = usePage();
const isOpen = ref(false);
const loadingCpf = ref(null);

// Lista dos usuários de demonstração criados no UsuarioSeeder
const devUsers = [
  { cpf: "00000000000", nome: "Root (Super Admin)", perfil: "Root", severity: "danger" },
  { cpf: "11111111111", nome: "Coordenação", perfil: "Administrador", severity: "warn" },
  { cpf: "22222222222", nome: "Prof. Dr. Docente", perfil: "Docente", severity: "info" },
  { cpf: "33333333333", nome: "Discente Aluno", perfil: "Discente", severity: "success" },
  {
    cpf: "44444444444",
    nome: "Técnico Administrativo",
    perfil: "Técnico",
    severity: "secondary",
  },
  {
    cpf: "55555555555",
    nome: "Docente + Admin",
    perfil: "Docente / Admin",
    severity: "contrast",
  },
];

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
        class="mb-3 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 p-4"
      >
        <div
          class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700"
        >
          <div class="flex items-center gap-2">
            <i class="pi pi-bolt text-amber-500 font-bold" />
            <span
              class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200"
            >
              Dev Switcher (1 Clique)
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
          <button
            v-for="user in devUsers"
            :key="user.cpf"
            type="button"
            :disabled="loadingCpf !== null"
            class="w-full text-left p-2.5 rounded-lg border border-gray-100 dark:border-gray-700 hover:border-emerald-500 dark:hover:border-emerald-500 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/30 transition-all flex items-center justify-between group disabled:opacity-50"
            @click="loginAs(user.cpf)"
          >
            <div class="flex flex-col">
              <span
                class="text-xs font-semibold text-gray-800 dark:text-gray-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-300"
              >
                {{ user.nome }}
              </span>
              <span class="text-[11px] text-gray-400 font-mono">
                CPF: {{ user.cpf }}
              </span>
            </div>
            <div class="flex items-center gap-2">
              <Tag :value="user.perfil" :severity="user.severity" class="!text-[10px]" />
              <i
                :class="[
                  loadingCpf === user.cpf
                    ? 'pi pi-spin pi-spinner text-emerald-600'
                    : 'pi pi-chevron-right text-gray-300 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-transform',
                  'text-xs',
                ]"
              />
            </div>
          </button>
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
