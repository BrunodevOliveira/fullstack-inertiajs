<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import Message from 'primevue/message';
import Toast from 'primevue/toast';
import DevSwitcher from '@/components/DevSwitcher.vue';

const form = useForm({
    cpf: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Acesso ao Sistema" />
    <Toast position="top-right" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <h1 class="text-4xl font-black tracking-tight text-emerald-600 dark:text-emerald-400">
                PINC
            </h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Programa Institucional de Iniciação Científica
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
            <div class="bg-white dark:bg-gray-800 py-8 px-6 shadow-xl rounded-2xl border border-gray-200/80 dark:border-gray-700 sm:px-10">
                <div class="mb-6 text-center">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                        Entrar na sua conta
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Utilize seu CPF e senha cadastrados
                    </p>
                </div>

                <!-- Mensagem de erro geral de credenciais se houver -->
                <Message v-if="form.errors.cpf" severity="error" :closable="false" class="mb-4">
                    {{ form.errors.cpf }}
                </Message>

                <form class="space-y-5" @submit.prevent="submit">
                    <!-- Campo CPF -->
                    <div class="flex flex-col gap-1.5">
                        <label for="cpf" class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            CPF
                        </label>
                        <InputText
                            id="cpf"
                            v-model="form.cpf"
                            type="text"
                            placeholder="000.000.000-00 ou apenas números"
                            :invalid="!!form.errors.cpf"
                            class="w-full"
                            autofocus
                        />
                    </div>

                    <!-- Campo Senha -->
                    <div class="flex flex-col gap-1.5">
                        <label for="password" class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Senha
                        </label>
                        <Password
                            id="password"
                            v-model="form.password"
                            :feedback="false"
                            toggle-mask
                            placeholder="Sua senha de acesso"
                            :invalid="!!form.errors.password"
                            class="w-full"
                            input-class="w-full"
                        />
                        <small v-if="form.errors.password" class="text-red-500 text-xs">
                            {{ form.errors.password }}
                        </small>
                    </div>

                    <!-- Lembrar de mim -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="remember"
                                v-model="form.remember"
                                :binary="true"
                            />
                            <label for="remember" class="text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                                Lembrar-me
                            </label>
                        </div>
                    </div>

                    <!-- Botão Entrar -->
                    <Button
                        type="submit"
                        label="Entrar no PINC"
                        icon="pi pi-sign-in"
                        :loading="form.processing"
                        class="w-full !bg-emerald-600 hover:!bg-emerald-700 !border-emerald-600 font-semibold"
                    />
                </form>
            </div>
        </div>

        <!-- Dev Switcher Flutuante -->
        <DevSwitcher />
    </div>
</template>