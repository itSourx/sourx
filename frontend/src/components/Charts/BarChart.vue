<template>
  <div class="chart-container">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js'
import { computed } from 'vue'

// Enregistrement des composants nécessaires
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

// Déclaration des props
const props = defineProps({
  data: {
    type: Object,
    required: true
  }
})

const chartData = computed(() => ({
  labels: Object.keys(props.data),
  datasets: [
    {
      label: 'Nombre de Documents',
      backgroundColor: '#36A2EB',
      data: Object.values(props.data)
    }
  ]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false
}
</script>

<style scoped>
.chart-container {
  width: 100%;
  height: 400px;
}
</style>
