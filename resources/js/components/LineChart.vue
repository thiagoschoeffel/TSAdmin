<script setup>
import VueApexCharts from 'vue3-apexcharts'

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  series: {
    type: Array,
    default: () => []
  },
  categories: {
    type: Array,
    default: () => []
  },
  height: {
    type: [Number, String],
    default: 300
  },
  colors: {
    type: Array,
    default: () => ['#2563eb']
  }
})

const chartOptions = {
  chart: {
    type: 'line',
    height: props.height,
    toolbar: {
      show: false
    },
    zoom: {
      enabled: false
    }
  },
  colors: props.colors,
  dataLabels: {
    enabled: false
  },
  stroke: {
    curve: 'smooth',
    width: 3
  },
  grid: {
    borderColor: '#f1f5f9',
    strokeDashArray: 3,
  },
  xaxis: {
    categories: props.categories,
    labels: {
      style: {
        colors: '#64748b',
        fontSize: '12px'
      }
    },
    axisBorder: {
      show: false
    },
    axisTicks: {
      show: false
    }
  },
  yaxis: {
    labels: {
      style: {
        colors: '#64748b',
        fontSize: '12px'
      },
      formatter: function(value) {
        return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 })
      }
    }
  },
  tooltip: {
    theme: 'light',
    y: {
      formatter: function(value) {
        return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 })
      }
    }
  },
  markers: {
    size: 4,
    colors: props.colors,
    strokeColors: '#fff',
    strokeWidth: 2,
    hover: {
      size: 6
    }
  }
}
</script>

<template>
  <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
    <h3 v-if="title" class="text-lg font-semibold text-slate-900 mb-4">{{ title }}</h3>
    <apexchart
      type="line"
      :options="chartOptions"
      :series="series"
      :height="height"
    />
  </div>
</template>
