import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'

export const useStatisticsStore = defineStore('statistics', () => {
  const statistics = ref({
    usersCount: 0,
    documentsCount: 0,
    foldersCount: 0,
    requestsCount: 0,
    teams: 0,
    requestsReasons: 0,
    usedSpace: 0,
  })
  const error = ref(null)

  // Fonction pour récupérer les statistiques
  const fetchStatistics = async () => {
    error.value = null
    try {
      const response = await axios.get('https://sourxhr-backend-5190c64de794.herokuapp.com/api/v1/statistics', {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
      statistics.value = response.data
    } catch (error) {
      error.value = 'Erreur lors de la récupération des statistiques'
      console.error('Erreur lors de la récupération des statistiques:', error)
    }
  }

  return {
    statistics,
    error,
    fetchStatistics,
  }
})
