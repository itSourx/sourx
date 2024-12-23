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
        '/folders', 
        {
          headers: {
            Authorization: `Bearer ${userStore.token}`,
          },
        }
      );
      const uniqueFolders = Array.from(
        new Map(response.data.map((folder) => [folder.id, folder])).values()
      );
  
      // Mettre à jour les folders avec les données filtrées
      folders.value = uniqueFolders;
  
      console.log('folders', folders.value);
    } catch (error) {
      console.error('Erreur lors de la récupération des dossiers:', error)
    }
  }

  // Créer un nouveau dossier
  const createFolder = async payload => {
    try {
      console.log(payload)
      const response = await axios.post(
        '/folders/create',
        payload,
      )
    } catch (error) {
      console.error('Erreur lors de la création du dossier:', error)
      throw error
    }
  }

  return { folders, getAllFolders, createFolder }
})
