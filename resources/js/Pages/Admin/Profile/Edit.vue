<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmModal from '@/components/ConfirmModal.vue';

const props = defineProps({
  user: Object,
});

const page = usePage();

const form = useForm({
  name: props.user?.name || '',
  email: props.user?.email || '',
  current_password: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.patch('/admin/profile');
};

const confirmDelete = ref(false);
const destroyAccount = () => {
  confirmDelete.value = true;
};
</script>

<template>
  <AdminLayout>
    <Head title="Meu perfil" />

    <section class="card mx-auto max-w-2xl space-y-8">
      <div class="space-y-3">
        <h1 class="text-2xl font-semibold text-slate-900">Gerenciar minha conta</h1>
        <p class="text-sm text-slate-500">Atualize suas informações pessoais, e-mail e senha.</p>
      </div>



      <form @submit.prevent="submit" class="space-y-4">
        <label class="form-label">
          Nome
          <input type="text" v-model="form.name" required autocomplete="name" class="form-input" />
          <span v-if="form.errors.name" class="text-sm font-medium text-rose-600">{{ form.errors.name }}</span>
        </label>

        <label class="form-label">
          <span class="flex items-center gap-2">
            E-mail
            <span :class="props.user?.email_verified_at ? 'badge-success' : 'badge-danger'">
              {{ props.user?.email_verified_at ? 'Verificado' : 'Não verificado' }}
            </span>
          </span>
          <input type="email" v-model="form.email" required autocomplete="email" class="form-input" />
          <span v-if="form.errors.email" class="text-sm font-medium text-rose-600">{{ form.errors.email }}</span>
        </label>

        <label class="form-label">
          Perfil de acesso
          <input type="text" :value="props.user?.role === 'admin' ? 'Administrador' : 'Usuário comum'" class="form-input" disabled readonly />
        </label>

        <div class="h-px bg-slate-200"></div>

        <div class="space-y-2">
          <h2 class="text-lg font-semibold text-slate-900">Alterar senha</h2>
          <p class="text-sm text-slate-500">Informe sua senha atual para definir uma nova. Deixe em branco para manter a senha existente.</p>
        </div>

        <label class="form-label">
          Senha atual
          <input type="password" v-model="form.current_password" autocomplete="current-password" class="form-input" />
          <span v-if="form.errors.current_password" class="text-sm font-medium text-rose-600">{{ form.errors.current_password }}</span>
        </label>

        <label class="form-label">
          Nova senha
          <input type="password" v-model="form.password" autocomplete="new-password" class="form-input" />
          <span v-if="form.errors.password" class="text-sm font-medium text-rose-600">{{ form.errors.password }}</span>
        </label>

        <label class="form-label">
          Confirmar nova senha
          <input type="password" v-model="form.password_confirmation" autocomplete="new-password" class="form-input" />
        </label>

        <button type="submit" class="btn-primary" :disabled="form.processing">Salvar alterações</button>
      </form>

      <div class="h-px bg-slate-200"></div>

      <div class="space-y-4 rounded-xl border border-rose-100 bg-rose-50 p-6">
        <div class="space-y-2">
          <h2 class="text-lg font-semibold text-rose-700">Excluir conta</h2>
          <p class="text-sm text-rose-600">Esta ação é permanente. Ao confirmar, sua conta será removida e você será desconectado imediatamente.</p>
        </div>
        <button type="button" class="btn-danger" :disabled="form.processing" @click="destroyAccount">Excluir minha conta</button>
        <ConfirmModal v-model="confirmDelete"
                      title="Excluir conta"
                      message="Tem certeza que deseja remover sua conta? Esta ação não pode ser desfeita."
                      confirm-text="Excluir"
                      variant="danger"
                      :processing="form.processing"
                      @confirm="() => form.delete('/admin/profile')" />
      </div>
    </section>
  </AdminLayout>

</template>

<style scoped>
.form-label { display:flex; flex-direction:column; gap:.5rem; font-weight:600; color:#334155 }
.form-input { border:1px solid #cbd5e1; border-radius:.5rem; padding:.5rem .75rem; }
.btn-primary { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; background:#2563eb; color:#fff; font-weight:600; }
.btn-danger { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; background:#e11d48; color:#fff; font-weight:600; }
.status { border:1px solid #cbd5e1; background:#f8fafc; border-radius:.5rem; padding:.5rem .75rem; }
.status-danger { border-color:#fecaca; background:#fff1f2; color:#b91c1c; }
.badge-success { display:inline-flex; align-items:center; gap:.375rem; background:#ecfeff; color:#047857; font-weight:700; padding:.125rem .5rem; border-radius:.375rem; }
.badge-danger { display:inline-flex; align-items:center; gap:.375rem; background:#fff1f2; color:#b91c1c; font-weight:700; padding:.125rem .5rem; border-radius:.375rem; }
</style>
