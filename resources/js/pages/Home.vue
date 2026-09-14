

<script setup>
import AppHeader from '../components/AppHeader.vue';

defineProps({
    title: {
        type: String,
        default: 'Home'
    }
})
</script>

<template>
   <AppHeader title="Título da página"/>
    <main class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900">
        <div class="text-center p-8 bg-white dark:bg-gray-800 rounded-xl shadow-md">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-4">
                {{ title }}
            </h1>
            <p class="text-gray-600 dark:text-gray-300">
                Inertia.js + Vue 3 configurado com sucesso!
            </p>
        </div>
    </main>
</template>
