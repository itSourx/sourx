<template>
  <div class="chart-container">
    <Pie :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Pie } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement, CategoryScale } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale)

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
      backgroundColor: ['#FFCE56', '#039487', '#FF6384', '#4BC0C0', '#9966FF', '#FF9F40'],
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
  height: 300px;
}
</style>
