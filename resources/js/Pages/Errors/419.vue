<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';
defineProps({ status: { type: Number, default: 419 } });
</script>

<template>
  <PublicLayout>
    <section class="error-card">
      <h1 class="error-title">Sessão expirada ({{ status }})</h1>
      <p class="error-message">Sua sessão expirou ou o token CSRF não é mais válido. Atualize a página ou faça login novamente.</p>
      <div class="actions">
        <Link class="btn-primary" href="/login">Fazer login</Link>
        <Link class="btn-ghost" href="/">Ir para a página inicial</Link>
      </div>
    </section>
  </PublicLayout>
</template>

<style scoped>
.error-card { border:1px solid #e2e8f0; background:#fff; border-radius:.75rem; padding:1.25rem; max-width:48rem; }
.error-title { font-size:1.5rem; font-weight:700; color:#0f172a; }
.error-message { margin-top:.5rem; color:#475569; }
.actions { margin-top:1rem; display:flex; gap:.75rem; }
.btn-primary { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; background:#2563eb; color:#fff; font-weight:600; }
.btn-ghost { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; border:1px solid #cbd5e1; color:#0f172a; }
</style>

