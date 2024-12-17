// stores/requestStore.js
import { ref } from 'vue'
import { defineStore } from 'pinia'
import { useAuthStore } from '@/stores/authStore'
import axios from 'axios'

export const useRequestStore = defineStore('request', () => {
  const requests = ref([])
  const loading = ref(false)
  const error = ref(null)
  const userStore = useAuthStore()

  // Récupérer toutes les demandes de l'utilisateur
  const fetchUserRequests = async (reload = false) => {
    if (!reload && requests.value.length > 0) return

    loading.value = true
    error.value = null
    try {
      const response = await axios.get(
        '/requests/',
        {
          headers: {
            Authorization: `Bearer ${userStore.token}`,
          },
        },
      )
      requests.value = response.data
    } catch (err) {
      error.value = err
      console.error('Erreur lors de la récupération des demandes:', err)
    } finally {
      loading.value = false
    }
  }

  // Créer une nouvelle demande
  const createRequest = async newRequest => {
    try {
      loading.value = true
      const response = await axios.post(
        '/requests/create/',
        newRequest,
        {
          headers: {
            Authorization: `Bearer ${userStore.token}`,
            'Content-Type': 'multipart/form-data',
          },
        },
      )
    } catch (err) {
      error.value = err
      console.error('Erreur lors de la création de la demande:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // Supprimer une demande
  const deleteRequest = async requestId => {
    try {
      await axios.delete(
        `/requests/delete/${requestId}/`,
        {
          headers: {
            Authorization: `Bearer ${userStore.token}`,
          },
        },
      )
    } catch (err) {
      console.error('Erreur lors de la suppression de la demande:', err)
      throw err // Renvoyer l'erreur pour permettre une gestion supplémentaire si nécessaire
    }
  }

  // Modifier une demande
  const updateRequest = async (requestId, requestUpdated) => {
    try {
      const response = await axios.put(
        `/requests/update/${requestId}/`,
        requestUpdated,
        {
          headers: {
            Authorization: `Bearer ${userStore.token}`,
          },
        },
      )

      console.log(response)
    } catch (err) {
      console.error('Erreur de mise à jour de la demande:', err)
      throw err
    }
  }

  const takeChargeRequest = async requestId => {
    try {
      const response = await axios.put(
        `/requests/takeChargeRequest/${requestId}/`,
        {
          headers: {
            Authorization: `Bearer ${userStore.token}`,
          },
        },
      )

      console.log(response)
    } catch (err) {
      console.error('Erreur de mise à jour de la demande:', err)
      throw err
    }
  }

  const acceptRequest = async formData => {
    const requestId = formData.get('requestId')
    try {
      const response = await axios.post(
        `/requests/accept/${requestId}/`,
        formData,
        {
          headers: {
            Authorization: `Bearer ${userStore.token}`,
            'Content-Type': 'multipart/form-data',
          },
        },
      )

      console.log(response)
    } catch (err) {
      console.error('Erreur de mise à jour de la demande:', err)
      throw err
    }
  }

  const rejectRequest = async requestId => {
    try {
      const response = await axios.put(
        `/requests/reject/${requestId}/`,
        {
          headers: {
            Authorization: `Bearer ${userStore.token}`,
          },
        },
      )

      console.log(response)
    } catch (err) {
      console.error('Erreur de mise à jour de la demande:', err)
      throw err
    }
  }

  // Valider une demande
  const validateRequest = async requestId => {
    try {
      const response = await axios.post(
        `/requests/validate/${requestId}/`,
        {},
        {
          headers: {
            Authorization: `Bearer ${userStore.token}`,
          },
        },
      )
      // Mettre à jour le statut de la demande localement après validation
      const index = requests.value.findIndex(
        request => request.id === requestId,
      )
      if (index !== -1) {
        requests.value[index] = response.data
      }
    } catch (err) {
      console.error('Erreur lors de la validation de la demande:', err)
      throw err
    }
  }

  return {
    requests,
    loading,
    error,
    fetchUserRequests,
    createRequest,
    acceptRequest,
    deleteRequest,
    updateRequest,
    takeChargeRequest,
    rejectRequest,
    validateRequest,
  }
})
