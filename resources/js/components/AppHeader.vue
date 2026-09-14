<script setup>
import { Head } from '@inertiajs/vue3'

defineProps({
    title: {
        type: String,
        default: 'Home'
    }
})
</script>

<template>
    <Head  :title="title" />
</template>