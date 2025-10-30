<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import InputDatePicker from '@/components/InputDatePicker.vue';
import Button from '@/components/Button.vue';
import HeroIcon from '@/components/icons/HeroIcon.vue';
import ReservationsBarChart from '@/components/ReservationsBarChart.vue';
import ProductionByMaterialBarChart from '@/components/ProductionByMaterialBarChart.vue';
import BlocksProducedByDayChart from '@/components/BlocksProducedByDayChart.vue';
import BlockProductionTable from '@/components/BlockProductionTable.vue';
import MoldedProductionAndScrapChart from '@/components/MoldedProductionAndScrapChart.vue';
import MoldedProductionRanking from '@/components/MoldedProductionRanking.vue';
import MoldedProductionYieldCard from '@/components/MoldedProductionYieldCard.vue';
import RawMaterialStockTable from '@/components/RawMaterialStockTable.vue';
import axios from 'axios';
import { route } from 'ziggy-js';

const props = defineProps({
    filters: { type: Object, default: () => ({}) },
    summary: { type: Object, required: true },
    siloLoads: { type: Array, default: () => [] },
});

const period = ref({ start: props.filters?.from || null, end: props.filters?.to || null });
const loading = ref(false);
const summary = ref(props.summary);
const silos = ref(props.siloLoads);

const rawMaterialStock = ref([]);
const rawMaterialStockLoading = ref(false);

const fetchRawMaterialStock = async () => {
    rawMaterialStockLoading.value = true;
    try {
        const params = {};
        if (period.value.start) params.from = period.value.start;
        if (period.value.end) params.to = period.value.end;
        const res = await axios.get(route('inventory.raw-material-stock'), { params });
        rawMaterialStock.value = res.data.data || [];
    } finally {
        rawMaterialStockLoading.value = false;
    }
};

const fetchData = () => {
    loading.value = true;
    router.get(route('inventory.dashboard'), {
        from: period.value.start,
        to: period.value.end,
    }, {
        preserveState: true,
        onFinish: () => {
            loading.value = false;
            fetchRawMaterialStock();
        },
    });
};

// Atualiza estoque ao montar e ao mudar período
fetchRawMaterialStock();

const fmt = (n, d = 2) => new Intl.NumberFormat('pt-BR', { minimumFractionDigits: d, maximumFractionDigits: d }).format(Number(n || 0));

const totalSilos = computed(() => silos.value.length);
const totalMateriaisEmSilos = computed(() => silos.value.reduce((acc, s) => acc + (s.materials?.length || 0), 0));
const totalCargaSilos = computed(() => silos.value.reduce((acc, s) => acc + s.materials?.reduce((sum, m) => sum + m.balance_kg, 0) || 0, 0));
</script>

<template>

    <Head title="Estoque - Resumo" />
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">Estoque - Resumo</h1>
                    <p class="text-sm text-slate-600 mt-1">Visão geral dos movimentos e cargas de estoque</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <InputDatePicker v-model="period.start" placeholder="Data inicial" size="sm" />
                        <InputDatePicker v-model="period.end" placeholder="Data final" size="sm" />
                    </div>
                    <Button :disabled="loading" @click="fetchData" size="sm">
                        <HeroIcon name="arrow-path" class="h-4 w-4 mr-2" />
                        Atualizar
                    </Button>
                </div>
            </div>

            <!-- Cards de Resumo -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <!-- Entrada MP -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Entrada MP</p>
                            <p class="text-2xl font-bold text-slate-900">{{ fmt(summary.raw_material_input_kg) }}</p>
                            <p class="text-xs text-slate-500">kg</p>
                        </div>
                    </div>
                </div>
                <!-- Consumo MP -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Consumo MP</p>
                            <p class="text-2xl font-bold text-slate-900">{{ fmt(summary.raw_material_consumed_kg) }}</p>
                            <p class="text-xs text-slate-500">kg</p>
                        </div>
                    </div>
                </div>
                <!-- Produção Blocos -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Produção Blocos</p>
                            <p class="text-2xl font-bold text-slate-900">{{ fmt(summary.blocks_produced_units, 0) }}</p>
                            <p class="text-xs text-slate-500">unidades</p>
                        </div>
                    </div>
                </div>
                <!-- Refugos -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Refugos Blocos</p>
                            <p class="text-2xl font-bold text-slate-900">{{ fmt(summary.block_loss_units, 0) }}</p>
                            <p class="text-xs text-slate-500">unidades</p>
                        </div>
                    </div>
                </div>
                <!-- Refugos kg -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Refugos Blocos</p>
                            <p class="text-2xl font-bold text-slate-900">{{ fmt(summary.block_loss_kg) }}</p>
                            <p class="text-xs text-slate-500">kg</p>
                        </div>
                    </div>
                </div>
                <!-- Produção Blocos m³ -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Produção Blocos m³</p>
                            <p class="text-2xl font-bold text-slate-900">{{ fmt(summary.blocks_produced_m3) }}</p>
                            <p class="text-xs text-slate-500">m³</p>
                        </div>
                    </div>
                </div>
                <!-- MP Virgem para Blocos -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">MP Virgem p/ Blocos</p>
                            <p class="text-2xl font-bold text-slate-900">{{ fmt(summary.virgin_mp_kg_for_blocks) }}</p>
                            <p class="text-xs text-slate-500">kg</p>
                        </div>
                    </div>
                </div>
                <!-- MP Reciclada para Blocos -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">MP Reciclada p/ Blocos</p>
                            <p class="text-2xl font-bold text-slate-900">{{ fmt(summary.recycled_mp_kg_for_blocks) }}
                            </p>
                            <p class="text-xs text-slate-500">kg</p>
                        </div>
                    </div>
                </div>
                <!-- Produção Moldados -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Produção Moldados</p>
                            <p class="text-2xl font-bold text-slate-900">{{ fmt(summary.molded_produced_units, 0) }}</p>
                            <p class="text-xs text-slate-500">unidades</p>
                        </div>
                    </div>
                </div>
                <!-- Refugos Moldados -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Refugos Moldados</p>
                            <p class="text-2xl font-bold text-slate-900">{{ fmt(summary.molded_loss_units, 0) }}</p>
                            <p class="text-xs text-slate-500">unidades</p>
                        </div>
                    </div>
                </div>
                <!-- MP Virgem p/ Moldados -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">MP Virgem p/ Moldados</p>
                            <p class="text-2xl font-bold text-slate-900">{{ fmt(summary.virgin_mp_kg_for_molded) }}</p>
                            <p class="text-xs text-slate-500">kg</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Reservas de Matéria-Prima -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                <ReservationsBarChart class="xl:col-span-1" />
                <ProductionByMaterialBarChart class="xl:col-span-1" />
            </div>

            <!-- Gráfico de Produção de Blocos por Dia -->
            <BlocksProducedByDayChart />

            <!-- Tabela de Produção de Blocos por Tipo e Dimensões -->
            <BlockProductionTable />

            <!-- Gráfico de Produção de Moldados por Dia -->
            <MoldedProductionAndScrapChart />

            <!-- Ranking de Refugos dos Moldados + Card de Aproveitamento -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                <div class="xl:col-span-1">
                    <MoldedProductionRanking :data="summary.molded_loss_ranking || []" />
                </div>
                <div class="xl:col-span-1">
                    <MoldedProductionYieldCard :produced="summary.molded_produced_units || 0"
                        :scrap="summary.molded_loss_units || 0" />
                </div>
            </div>
        </div>

        <!-- Cargas por Silo -->
        <div class="bg-white rounded-lg border border-slate-200 p-6 mt-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Cargas por silo</h2>
                    <p class="text-sm text-slate-600 mt-1">Materiais armazenados atualmente nos silos</p>
                </div>
            </div>

            <div v-if="silos.length === 0" class="text-center py-8">
                <p class="text-slate-500">Nenhum silo cadastrado</p>
            </div>

            <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="silo in silos" :key="silo.silo_id" class="border border-slate-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-slate-900">{{ silo.silo_name }}</h3>
                        <span class="text-xs text-slate-600 bg-slate-100 px-2 py-1 rounded">
                            {{ silo.materials?.length || 0 }} materiais
                        </span>
                    </div>

                    <div v-if="!silo.materials || silo.materials.length === 0"
                        class="text-center py-4 text-slate-500 text-sm">
                        Sem carga registrada
                    </div>

                    <div v-else class="space-y-2">
                        <div v-for="material in silo.materials" :key="material.raw_material_id"
                            class="flex items-center justify-between py-2 px-3 bg-slate-50 rounded">
                            <span class="text-sm text-slate-700">{{ material.raw_material_name }}</span>
                            <span class="text-sm text-slate-900">
                                {{ fmt(material.balance_kg) }} kg
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- fechamento da div removido para corrigir erro de tag -->

        <!-- Tabela de estoque atual de matéria-prima -->
        <RawMaterialStockTable :period="period" :data="rawMaterialStock" :loading="rawMaterialStockLoading" />
    </AdminLayout>
</template>
