<script setup>
import ErrorLayoutSelector from '@/components/ErrorLayoutSelector.vue'
import { Link } from '@inertiajs/vue3'
import { route } from '@/ziggy-client'
import { computed } from 'vue'

const props = defineProps({ status: { type: Number, default: 404 }, url: { type: String, default: null } })

const backHref = computed(() => {
  const u = props.url || (typeof window !== 'undefined' ? window.location.pathname : '/')
  if (u.startsWith('/admin')) return route('dashboard')
  return route('home')
})
</script>

<template>
  <ErrorLayoutSelector>
    <section class="error-card">
      <h1 class="error-title">Página não encontrada ({{ props.status }})</h1>
      <p class="error-message">A página que você procura pode ter sido removida, renomeada ou está temporariamente indisponível.</p>
      <div class="actions">
        <Link class="btn-primary" :href="backHref">Voltar para a página inicial</Link>
      </div>
    </section>
  </ErrorLayoutSelector>
</template>

<style scoped>
.error-card { border:1px solid #e2e8f0; background:#fff; border-radius:.75rem; padding:1.25rem; max-width:48rem; }
.error-title { font-size:1.5rem; font-weight:700; color:#0f172a; }
.error-message { margin-top:.5rem; color:#475569; }
.actions { margin-top:1rem; display:flex; gap:.75rem; }
.btn-primary { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; background:#2563eb; color:#fff; font-weight:600; }
</style>

