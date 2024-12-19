import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'

export const useTeamStore = defineStore('team', () => {
  const teams = ref([])
  const selectedTeam = ref(null)
  const isLoading = ref(false)

  // Récupérer toutes les équipes
  const fetchTeams = async (reload = false) => {
    if (!reload && teams.value.length > 0) return
    isLoading.value = true
    try {
      const response = await axios.get('/teams')
      console.log(response)
      teams.value = response.data
    } catch (error) {
      console.error('Erreur lors de la récupération des équipes:', error)
    } finally {
      isLoading.value = false
    }
  }

  // Créer une nouvelle équipe
  async function createTeam(newTeam) {
    try {
      const response = await axios.post(
        '/teams/create',
        newTeam,
      )
      teams.value.push(response.data)
    } catch (error) {
      console.error("Erreur lors de la création de l'équipe:", error)
      throw error
    }
  }

  // Mettre à jour une équipe existante
  async function updateTeam(teamId, updatedData) {
    try {
      const response = await axios.patch(
        `/teams/update/${teamId}`,
        updatedData,
      )
    } catch (error) {
      console.error("Erreur lors de la mise à jour de l'équipe:", error)
      throw error
    }
  }

  // Mettre à jour le statut actif/inactif d'une équipe
  async function updateTeamStatus(teamId, newStatut) {
    try {
      const response = await axios.patch(
        `/teams/${teamId}/status`,
        {
          status: newStatut,
        },
      )
      const index = teams.value.findIndex(team => team.id === teamId)
      if (index !== -1) {
        teams.value[index].fields.active = response.data.active
      }
    } catch (error) {
      console.error(
        "Erreur lors de la mise à jour du statut de l'équipe:",
        error,
      )
      throw error
    }
  }

  // Supprimer une équipe
  async function deleteTeam(teamId) {
    try {
      await axios.delete(`/teams/${teamId}`)
      teams.value = teams.value.filter(team => team.id !== teamId)
    } catch (error) {
      console.error("Erreur lors de la suppression de l'équipe:", error)
      throw error
    }
  }

  // Sélectionner une équipe pour édition
  function selectTeam(team) {
    selectedTeam.value = team
  }

  // Vider la sélection d'équipe
  function clearSelectedTeam() {
    selectedTeam.value = null
  }

  // Computed pour vérifier si une équipe est sélectionnée
  const isTeamSelected = computed(() => !!selectedTeam.value)

  return {
    teams,
    selectedTeam,
    isLoading,
    fetchTeams,
    createTeam,
    updateTeam,
    updateTeamStatus,
    deleteTeam,
    selectTeam,
    clearSelectedTeam,
    isTeamSelected,
  }
})
