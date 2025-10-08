<script setup>
import ErrorLayoutSelector from '@/components/ErrorLayoutSelector.vue'
import { Link } from '@inertiajs/vue3'
import { route } from '@/ziggy-client'
import { computed } from 'vue'

const props = defineProps({ status: { type: Number, default: 419 }, url: { type: String, default: null } })

const backHref = computed(() => {
  const u = props.url || (typeof window !== 'undefined' ? window.location.pathname : '/')
  if (u.startsWith('/admin')) return route('dashboard')
  return route('home')
})
</script>

<template>
  <ErrorLayoutSelector>
    <section class="card max-w-3xl mx-auto">
      <h1 class="error-title">Sessão expirada ({{ props.status }})</h1>
      <p class="error-message">Sua sessão expirou ou o token CSRF não é mais válido. Atualize a página ou faça login novamente.</p>
      <div class="actions">
        <Link class="btn-primary" href="/login">Fazer login</Link>
        <Link class="btn-ghost" :href="backHref">Ir para a página inicial</Link>
      </div>
    </section>
  </ErrorLayoutSelector>
</template>

<style scoped>
.error-title { font-size:1.5rem; font-weight:700; color:#0f172a; }
.error-message { margin-top:.5rem; color:#475569; }
.actions { margin-top:1rem; display:flex; gap:.75rem; }
.btn-primary { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; background:#2563eb; color:#fff; font-weight:600; }
.btn-ghost { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; border:1px solid #cbd5e1; color:#0f172a; }
</style>

