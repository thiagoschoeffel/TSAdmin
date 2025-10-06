<script setup>
import { usePage, Link } from '@inertiajs/vue3';
const page = usePage();
defineProps({
    title: { type: String, default: 'Inertia Demo' }
});
</script>

<template>
    <div class="container-default py-10">
        <h1 class="text-2xl font-semibold text-slate-900">{{ title }}</h1>
        <p class="mt-2 text-slate-600">Este é um exemplo inicial usando Inertia + Vue 3.</p>

        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-800">Usuário autenticado</h2>
            <pre class="mt-2 overflow-auto rounded bg-slate-50 p-3 text-xs text-slate-700">{{ page.props.auth }}</pre>
        </div>

        <div class="mt-6">
            <Link :href="route('dashboard')" class="btn-ghost">Voltar para o dashboard</Link>
        </div>
    </div>

</template>

<style scoped>
.container-default { max-width: 72rem; margin: 0 auto; padding-left: 1rem; padding-right: 1rem; }
.btn-ghost { display: inline-flex; align-items: center; gap: .5rem; padding: .5rem .75rem; border-radius: .5rem; border: 1px solid #cbd5e1; color: #0f172a; }
</style>

