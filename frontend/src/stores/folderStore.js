import { ref } from 'vue'
import { defineStore } from 'pinia'
import { useAuthStore } from '@/stores/authStore'
import axios from 'axios'

export const useFolderStore = defineStore('folder', () => {
  const folders = ref([])
  const userStore = useAuthStore()

  // Récupérer tous les dossiers
  const getAllFolders = async (reload = false) => {
    if (!reload && folders.value.length > 0) return

    try {
      const response = await axios.get(
        'http://localhost:8000/api/v1/folders/',
        {
          headers: {
            Authorization: `Bearer ${userStore.token}`,
          },
        },
      )
      folders.value = response.data
      console.log('folders', folders.value)
    } catch (error) {
      console.error('Erreur lors de la récupération des dossiers:', error)
    }
  }

  // Créer un nouveau dossier
  const createFolder = async payload => {
    try {
      console.log(payload)
      const response = await axios.post(
        'http://localhost:8000/api/v1/folders/create',
        payload,
      )
    } catch (error) {
      console.error('Erreur lors de la création du dossier:', error)
      throw error
    }
  }

  return { folders, getAllFolders, createFolder }
})
