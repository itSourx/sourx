import { defineStore } from 'pinia'
import axios from 'axios'
import { useDocumentStore } from '@/stores/UserStore/DocumentsStore'
import { toast } from 'vue3-toastify'

export const useFolderStore = defineStore('folder', {
  state: () => ({
    folders: []
  }),
  actions: {
    async fetchFolderNames() {
      const documentStore = useDocumentStore()
      
      if (!documentStore.documentsLoaded) {
        await documentStore.fetchDocuments(localStorage.getItem('jwt_token'))
      }

      const allFolders = documentStore.allFolders

      if (allFolders && allFolders.length > 0) {
        const folderIcons = [
          '/src/assets/folder_icon.svg',
          '/src/assets/folder_green_icon.svg',
          '/src/assets/folder_document_icon.svg',
          '/src/assets/folder_yellow_icon.svg' // Default icon for all other folders
        ]

        this.folders = allFolders.map((folder, index) => ({
          id: folder.id,
          logo: folderIcons[index] || folderIcons[3],
          title: folder.name,
          documents: [], 
          size: 0
        }))

        console.log('FOLDERS FROM DOCUMENTSTORE')
        console.log(this.folders)

        localStorage.setItem('folders', JSON.stringify(this.folders))
      } else {
        console.error('Aucun dossier trouvé dans DocumentStore')
        toast.error('Erreur lors du chargement des dossiers.')
      }
    },
    getFolderById(folderId) {
      return this.folders.find((folder) => folder.id === parseInt(folderId))
    },
    async createFolder(folderName) {

      if (!folderName || folderName.trim() === '') {
        toast.error('Le nom du dossier ne peut pas être vide.')
        return 
      }
      
      try {
        const response = await axios.post(
          'https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/folders/create',
          {
            Name: folderName
          },
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('jwt_token')}`
            }
          }
        )

        if (response.status === 200) {
          this.folders.push({
            id: this.folders.length + 1,
            logo: '/src/assets/folder_yellow_icon.svg',
            title: folderName,
            documents: [],
            size: 0
          })

          localStorage.setItem('folders', JSON.stringify(this.folders))
          toast.success(response.data.message)
          return response.data
        }
      } catch (error) {
        toast.error(error.response.data.message)
      }
    },
    async deleteFolder(folderToRemove) {
      try {
        const response = await axios.delete(
          `https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/folders/delete/${folderToRemove.name}`,
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('jwt_token')}`
            }
          }
        )

        if (response.status === 200) {
          this.folders = this.folders.filter((folder) => folder.id !== folderToRemove.id)
          localStorage.setItem('folders', JSON.stringify(this.folders))
          toast.success(response.data.message)
        }
      } catch (error) {
        toast.error(error.response.data.message)
        console.error('Error deleting folder:', error)
      }
    },
    async renameFolder(folderToRename, newName) {
      try {
        if (folderToRename) {
          console.log(folderToRename)
          const response = await axios.put(
            `https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/folders/rename/${folderToRename.name}`,
            { name: newName },
            {
              headers: {
                Authorization: `Bearer ${localStorage.getItem('jwt_token')}`
              }
            }
          )

          if (response.status === 200) {
            folderToRename.name = newName
            localStorage.setItem('folders', JSON.stringify(this.folders))
            toast.success(response.data.message)
          }
        } else {
          toast.error('Dossier non trouvé.')
        }
      } catch (error) {
        toast.error(error.response.data.message)
        console.error('Error renaming folder:', error)
      }
    },
    loadFoldersFromStorage() {
      const storedFolders = localStorage.getItem('folders')
      if (storedFolders) {
        this.folders = JSON.parse(storedFolders)
      }
    },
    clearFolders() {
      this.folders = []
      localStorage.removeItem('folders')
    }
  }
})
