<template>
  <ErrorLayoutSelector>
    <div class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-700">500</h1>
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Erro interno</h2>
        <p class="text-gray-600 mb-8">Ocorreu um erro interno. Tente novamente mais tarde.</p>
        <Link :href="backHref" class="btn-primary">Voltar ao início</Link>
      </div>
    </div>
  </ErrorLayoutSelector>
</template>

<script setup>
import ErrorLayoutSelector from '@/components/ErrorLayoutSelector.vue'
import { Link } from '@inertiajs/vue3'
import { route } from '@/ziggy-client'
import { computed } from 'vue'

const props = defineProps({ url: { type: String, default: null } })

const backHref = computed(() => {
  const u = props.url || (typeof window !== 'undefined' ? window.location.pathname : '/')
  if (u.startsWith('/admin')) return route('dashboard')
  return route('home')
})
</script>

<style scoped>
.btn-primary { background:#3b82f6;color:#fff;padding:.75rem 1.25rem;border-radius:.5rem;text-decoration:none }
</style>
