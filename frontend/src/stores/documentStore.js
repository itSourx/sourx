import { ref } from 'vue'
import { defineStore } from 'pinia'
import { useAuthStore } from '@/stores/authStore'
import axios from 'axios'

export const useDocumentStore = defineStore('document', () => {
  const documents = ref([])
  const perPage = ref(10)
  const totalDocuments = ref(0)
  const userStore = useAuthStore()

  const getAllDocuments = async (page = 1, reload = false) => {
    if (!reload && documents.value.length > 0) return

    try {
      const response = await axios.get(
        '/documents',
        {
          params: { page, per_page: perPage.value },
          headers: { Authorization: `Bearer ${userStore.token}` },
        },
      )
      if (response.data && response.data.files) {
        documents.value = response.data.files
        totalDocuments.value = parseInt(response.data.total) || 0
      }
    } catch (error) {
      console.error('Erreur lors de la récupération des documents:', error)
      throw error // Vous pouvez afficher une notification ici si nécessaire
    }
  }

  // Créer un nouveau document
  const createDocument = async payload => {
    try {
      const response = await axios.post(
        '/documents/create',
        payload,
        {
          headers: {
            Authorization: `Bearer ${userStore.token}`,
          },
        },
      )
      documents.value.push(response.data.document)
    } catch (error) {
      console.error('Erreur lors de la création du dossier:', error)
      throw error
    }
  }

  const removeFile = async fileId => {
    try {
      await axios.delete(
        `/documents/delete/${fileId}`,
        {
          headers: {
            Authorization: `Bearer ${userStore.token}`,
          },
        },
      )

      documents.value = documents.value.filter(doc => doc.id !== fileId)
    } catch (error) {
      console.error('Erreur lors de la suppression du document:', error)
      throw error // Vous pouvez gérer les erreurs ici comme vous le souhaitez
    }
  }

  return {
    documents,
    totalDocuments,
    getAllDocuments,
    createDocument,
    removeFile,
  }
})
