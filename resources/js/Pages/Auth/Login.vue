<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/components/ui/Checkbox.vue';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post('/login');
};
</script>

<template>
  <PublicLayout>
    <section class="card space-y-6 max-w-xl">
      <h1 class="text-2xl font-semibold text-slate-900">Entrar</h1>

      <form @submit.prevent="submit" class="space-y-4">
        <label class="form-label">
          E-mail
          <input type="email" v-model="form.email" required autocomplete="email" class="form-input" />
          <span v-if="form.errors.email" class="text-sm font-medium text-rose-600">{{ form.errors.email }}</span>
        </label>

        <label class="form-label">
          Senha
          <input type="password" v-model="form.password" required autocomplete="current-password" class="form-input" />
          <span v-if="form.errors.password" class="text-sm font-medium text-rose-600">{{ form.errors.password }}</span>
        </label>

        <Checkbox v-model="form.remember">Manter conectado</Checkbox>

        <div class="flex flex-wrap items-center gap-3">
          <button type="submit" :disabled="form.processing" class="btn-primary">
            <span v-if="!form.processing">Entrar</span>
            <span v-else>Enviando…</span>
          </button>
          <Link class="btn-ghost" href="/register">Criar uma conta</Link>
        </div>
      </form>
    </section>
  </PublicLayout>
  
</template>

<style scoped>
.card { border:1px solid #e2e8f0; background:#fff; border-radius: .75rem; padding: 1.25rem; }
.form-label { display:flex; flex-direction:column; gap:.5rem; font-weight:600; color:#334155 }
.form-input { border:1px solid #cbd5e1; border-radius:.5rem; padding:.5rem .75rem; }
.btn-primary { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; background:#2563eb; color:#fff; font-weight:600; }
.btn-ghost { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; border:1px solid #cbd5e1; color:#0f172a; }
</style>
