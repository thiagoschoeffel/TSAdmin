<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ProductForm from '@/components/products/ProductForm.vue';

const props = defineProps({
  products: { type: Array, required: true },
});

const form = useForm({
  name: '',
  description: '',
  price: '',
  status: 'active',
  components: [],
});

const submit = () => {
  form.post('/admin/products');
};
</script>

<template>
  <AdminLayout>
    <Head title="Novo produto" />

    <section class="card space-y-8">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900">Cadastrar produto</h1>
          <p class="mt-2 text-sm text-slate-500">Preencha os dados para registrar um novo produto.</p>
        </div>
      </div>

      <ProductForm :form="form" :products="props.products" :submit-label="'Salvar produto'" :cancel-href="route('products.index')" @submit="submit" />
    </section>
  </AdminLayout>
</template>
