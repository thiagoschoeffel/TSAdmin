<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ProductForm from '@/components/products/ProductForm.vue';

const props = defineProps({
  product: { type: Object, required: true },
  products: { type: Array, required: true },
});

// Preparar os componentes no formato esperado pelo form
const components = props.product.components?.map(component => ({
  id: component.id,
  quantity: component.pivot.quantity,
})) || [];

const form = useForm({
  name: props.product.name,
  description: props.product.description,
  price: props.product.price,
  status: props.product.status,
  components: components,
});

const submit = () => {
  form.put(`/admin/products/${props.product.id}`);
};
</script>

<template>
  <AdminLayout>
    <Head title="Editar produto" />

    <section class="card space-y-8">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900">Editar produto</h1>
          <p class="mt-2 text-sm text-slate-500">Atualize os dados do produto selecionado.</p>
        </div>
      </div>

      <ProductForm
        :form="form"
        :products="props.products"
        :submit-label="'Salvar alterações'"
        :cancel-href="route('products.index')"
        :is-editing="true"
        :product-id="props.product.id"
        @submit="submit"
      />
    </section>
  </AdminLayout>
</template>
