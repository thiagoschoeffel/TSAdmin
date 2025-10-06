<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PermissionsMatrix from '@/components/users/PermissionsMatrix.vue';
import Switch from '@/components/ui/Switch.vue';

const props = defineProps({
  resources: { type: Object, required: true },
});

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  status: 'active',
  role: 'user',
  permissions: {},
  modules: {},
});

const submit = () => {
  form.post('/admin/users');
};
</script>

<template>
  <AdminLayout>
    <Head title="Novo usuário" />

    <section class="card space-y-8">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900">Novo usuário</h1>
          <p class="mt-2 text-sm text-slate-500">Preencha os dados para cadastrar um novo membro.</p>
        </div>
  <Link class="btn-ghost" :href="route('users.index')">Voltar para lista</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2">
          <label class="form-label">
            Nome
            <input type="text" v-model="form.name" required autocomplete="name" class="form-input" />
            <span v-if="form.errors.name" class="text-sm font-medium text-rose-600">{{ form.errors.name }}</span>
          </label>

          <label class="form-label">
            E-mail
            <input type="email" v-model="form.email" required autocomplete="email" class="form-input" />
            <span v-if="form.errors.email" class="text-sm font-medium text-rose-600">{{ form.errors.email }}</span>
          </label>

          <div class="switch-field sm:col-span-2">
            <span class="switch-label">Status do usuário</span>
            <Switch v-model="form.status" true-value="active" false-value="inactive" />
            <span class="switch-status" :class="{ 'inactive': form.status !== 'active' }">
              {{ form.status === 'active' ? 'Ativo' : 'Inativo' }}
            </span>
          </div>
        </div>

        <fieldset class="space-y-3">
          <legend class="text-sm font-semibold text-slate-700">Credenciais de acesso</legend>
          <div class="grid gap-4 sm:grid-cols-2">
            <label class="form-label">
              Senha
              <input type="password" v-model="form.password" required autocomplete="new-password" class="form-input" />
              <span v-if="form.errors.password" class="text-sm font-medium text-rose-600">{{ form.errors.password }}</span>
            </label>
            <label class="form-label">
              Confirmar senha
              <input type="password" v-model="form.password_confirmation" required autocomplete="new-password" class="form-input" />
            </label>
          </div>
        </fieldset>

        <div class="space-y-2">
          <span class="text-sm font-semibold text-slate-700">Perfil de acesso</span>
          <div class="grid gap-4 sm:grid-cols-2">
            <label class="form-label">
              Função
              <select v-model="form.role" class="form-select" required>
                <option value="user">Usuário comum</option>
                <option value="admin">Administrador</option>
              </select>
            </label>
            <p class="text-sm text-slate-500">
              Administradores podem gerenciar usuários. Demais perfis possuem acesso restrito às próprias operações.
            </p>
          </div>
          <span v-if="form.errors.role" class="text-sm font-medium text-rose-600">{{ form.errors.role }}</span>
        </div>

        <PermissionsMatrix :resources="props.resources"
                           :role="form.role"
                           v-model="form.permissions"
                           v-model:modules="form.modules" />

        <div class="flex flex-wrap gap-3">
          <button type="submit" class="btn-primary" :disabled="form.processing">Salvar</button>
          <Link class="btn-ghost" :href="route('users.index')">Cancelar</Link>
        </div>
      </form>
    </section>
  </AdminLayout>
</template>
