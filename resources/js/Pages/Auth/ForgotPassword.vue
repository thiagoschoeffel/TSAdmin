<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({ email: '' });
const submit = () => form.post('/forgot-password');
</script>

<template>
  <PublicLayout>
    <section class="card max-w-md mx-auto">
      <h1 class="text-2xl font-semibold mb-4">Esqueci minha senha</h1>
      <p class="mb-4">Digite seu e-mail e enviaremos um link para redefinir sua senha.</p>
      <form @submit.prevent="submit" class="space-y-4">
        <label class="form-label">
          E-mail
          <input type="email" v-model="form.email" required class="form-input" />
          <span v-if="form.errors.email" class="text-sm font-medium text-rose-600">{{ form.errors.email }}</span>
        </label>
        <div class="flex gap-3">
          <button class="btn-primary" :disabled="form.processing">Enviar link</button>
          <Link class="text-sm text-slate-500" href="/login">Voltar ao login</Link>
        </div>
      </form>
    </section>
  </PublicLayout>
</template>
