<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const sending = ref(false);
const resend = async () => {
  if (sending.value) return;
  sending.value = true;
  try {
    await router.post('/email/verification-notification');
  } finally {
    sending.value = false;
  }
};
</script>

<template>
  <PublicLayout>
    <section class="card space-y-6 max-w-xl">
      <h1 class="text-2xl font-semibold mb-4">Confirme seu e-mail</h1>
      <p class="mb-4">Verificamos seu cadastro. Por favor, verifique seu e-mail e clique no link de confirmação enviado para você.</p>
      <div class="flex gap-3">
  <button class="btn-primary" :disabled="sending" @click="resend">Reenviar link de verificação</button>
        <Link class="text-sm text-slate-500" :href="route('login')">Voltar ao login</Link>
      </div>
    </section>
  </PublicLayout>
</template>
