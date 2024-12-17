import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useAuthStore } from '@/stores/authStore'
import axios from 'axios'

export const useCompanyStore = defineStore('company', () => {
  const companyInfo = ref(null)
  const isLoading = ref(false)
  const userStore = useAuthStore()

  // Récupérer les informations de l'entreprise
  async function fetchCompanyInfo() {
    isLoading.value = true
    try {
      const response = await axios.get('http://localhost:8000/api/v1/company')
      companyInfo.value = response.data
      console.log(companyInfo.value)
    } catch (error) {
      console.error(
        "Erreur lors de la récupération des informations de l'entreprise:",
        error,
      )
    } finally {
      isLoading.value = false
    }
  }

  // Mettre à jour les informations de l'entreprise
  async function updateCompany(updatedData) {
    try {
      await axios.patch(
        'http://localhost:8000/api/v1/company/update',
        updatedData,
      )
      companyInfo.value = { ...companyInfo.value, ...updatedData }
    } catch (error) {
      console.error(
        "Erreur lors de la mise à jour des informations de l'entreprise:",
        error,
      )
      throw error
    }
  }

  // Télécharger une image (signature ou logo)
  async function uploadFile(formData, type) {
    try {
      console.log(formData)
      console.log('/////////------////')
      const response = await axios.post(
        `http://localhost:8000/api/v1/company/upload-${type}`,
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data',
            Authorization: `Bearer ${userStore.token}`,
          },
        },
      )
      console.log('////////////////////')
      console.log(response)
      /* companyInfo.value.fields[type] = response.data.url  */ // Mise à jour de l'URL dans les données de l'entreprise
    } catch (error) {
      console.error(`Erreur lors du téléchargement du fichier ${type}:`, error)
      throw error
    }
  }

  const isCompanyLoaded = computed(() => !!companyInfo.value)

  return {
    companyInfo,
    isLoading,
    fetchCompanyInfo,
    updateCompany,
    uploadFile,
    isCompanyLoaded,
  }
})
