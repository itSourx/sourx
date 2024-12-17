// stores/requestStore.js
import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'

export const useRequestReasons = defineStore('requestReasons', () => {
  const requestReasons = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function fetchRequestReasons(reload = false) {
    if (!reload && requestReasons.value.length > 0) {
      return requestReasons.value
    }

    loading.value = true
    error.value = null
    try {
      const response = await axios.get(
        '/requestReasons',
      )
      requestReasons.value = response.data.reasons
        .map(reason => ({
          reason_title: reason.fields.name,
          request_reason_id: reason.fields.request_reason_id,
          createdTime: reason.createdTime,
          id: reason.id,
          pdf_model: reason.fields.pdf_model,
          status: reason.fields.status === 'active',
        }))
        .sort((a, b) => a.reason_title.localeCompare(b.reason_title))
    } catch (err) {
      error.value = err
      console.error(
        'Erreur lors de la récupération des raisons de demande:',
        error.value,
      )
    } finally {
      loading.value = false
    }
    return requestReasons.value
  }

  async function addRequestReason(newReason) {
    try {
      await axios.post(
        '/requestReasons/create',
        newReason,
      )
      await fetchRequestReasons(true)
    } catch (err) {
      console.error("Erreur d'ajout du motif:", err)
    }
  }

  async function updateRequestReason(updatedReason) {
    try {
      await axios.put(
        `/requestReasons/update/${updatedReason.id}`,
        updatedReason,
      )
      await fetchRequestReasons(true)
    } catch (err) {
      console.error('Erreur de mise à jour du motif:', err)
    }
  }

  async function deleteRequestReason(id) {
    try {
      await axios.delete(`/requestReasons/${id}`)
      await fetchRequestReasons(true)
    } catch (err) {
      console.error('Erreur de suppression du motif:', err)
    }
  }

  return {
    requestReasons,
    loading,
    error,
    fetchRequestReasons,
    addRequestReason,
    updateRequestReason,
    deleteRequestReason,
  }
})
