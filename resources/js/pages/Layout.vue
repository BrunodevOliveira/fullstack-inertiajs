<script setup>
import { Link } from "@inertiajs/vue3"
</script>

<template>
    <main>
        <header>
            <Link href="/">Home</Link>
            <Link href="/about">About</Link>
            <Link href="/contact">Contact</Link>
        </header>
        <article>
          <!-- Funciona como o @yeld() do Blade -->
            <slot />
        </article>
    </main>
</template>

