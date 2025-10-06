<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="text-center">
      <h1 class="text-9xl font-bold text-gray-300">404</h1>
      <h2 class="text-3xl font-semibold text-gray-700 mb-4">Página não encontrada</h2>
      <p class="text-gray-600 mb-8">Desculpe, a página que você está procurando não existe.</p>
      <Link :href="route('home')" class="btn-primary">
        Voltar ao início
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<style scoped>
.btn-primary {
  background-color: #3b82f6;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  text-decoration: none;
  font-weight: 600;
  transition: background-color 0.2s;
  display: inline-block;
}

.btn-primary:hover {
  background-color: #2563eb;
}
</style>
