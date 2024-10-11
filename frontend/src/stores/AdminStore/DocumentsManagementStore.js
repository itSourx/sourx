import { defineStore } from 'pinia'
import axios from 'axios'
import { toast } from 'vue3-toastify'

export const useAdminDocumentStore = defineStore('adminDocumentStore', {
  state: () => ({
    folders: [],
    files: [],
    documentsLoaded: false
  }),
  actions: {
    // Charger les dossiers et les fichiers
    async fetchFoldersAndFiles(token) {

      if (this.documentsLoaded) {
        console.log('Documents déjà chargés, pas besoin de recharger.');
        return;
      }

      try {
        const response = await axios.post('http://localhost:8000/api/v1/getDocumentByUser', {}, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        })
        const documents = response.data.documents
        console.log(documents)

        // Réinitialiser les dossiers et fichiers
        this.folders = []
        this.files = []

        // Organiser les documents par dossiers
        const folderMap = new Map()

        documents.forEach(doc => {
          const folderName = doc.folder || 'Racine';
          
          if (!folderMap.has(folderName)) {
            folderMap.set(folderName, {
              id: folderMap.size + 1,
              name: folderName,
              documents: [] // Assurez-vous que chaque dossier a cette propriété
            });
          }
        
          folderMap.get(folderName).documents.push({
            id: doc.id,
            name: doc.title,
            dateAdded: new Date(doc.createdTime).toLocaleDateString(),
            size: doc.size,
            url: doc.url
          });
        
          this.files.push({
            id: doc.id,
            name: doc.title,
            dateAdded: new Date(doc.createdTime).toLocaleDateString(),
            size: doc.size,
            url: doc.url
          });
        });

        this.folders = Array.from(folderMap.values())

        // Mettre à jour le localStorage après récupération des documents
        localStorage.setItem('folders', JSON.stringify(this.folders))
        this.documentsLoaded = true; 
        
      } catch (error) {
        console.error('Error fetching documents for folders:', error)
      }
    },

    async refreshDocuments() {
      const token = localStorage.getItem('jwt_token');
      await this.fetchFoldersAndFiles(token); // Recharge les dossiers et fichiers
    },

    // Créer un nouveau dossier
    async createFolder(folderName) {

      if (!folderName || folderName.trim() === '') {
        toast.error('Le nom du dossier ne peut pas être vide.')
        return 
      }

      try {
        const token = localStorage.getItem('jwt_token')
        const response = await axios.post(
          'http://localhost:8000/api/v1/folders/create',
          { Name: folderName },
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )

        if (response.status === 200) {
          this.folders.push({
            id: this.folders.length + 1,
            name: folderName,
            documents: []
          })
        }

        toast.success(response.data.message)

      } catch (error) {
        toast.error(error.response.data.message)
        console.error('Erreur lors de la création du dossier:', error)
      }
    },

    // Renommer un dossier ou un fichier
    async renameItem(itemId, newName, itemType) {
      try {
        const token = localStorage.getItem('jwt_token')
        const response = await axios.post(
          `http://localhost:8000/api/v1/${itemType}/rename`,
          {
            id: itemId,
            newName
          },
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )

        if (response.status === 200) {
          if (itemType === 'folder') {
            const folder = this.folders.find((folder) => folder.id === itemId)
            if (folder) folder.title = newName
          } else if (itemType === 'file') {
            const file = this.files.find((file) => file.id === itemId)
            if (file) file.name = newName
          }
          toast.success(`${itemType === 'folder' ? 'Dossier' : 'Fichier'} renommé avec succès`)
        } else {
          toast.error('Erreur lors du renommage')
        }
      } catch (error) {
        console.error('Erreur lors du renommage:', error)
      }
    },

    // Supprimer un dossier
    async removeFolder(folderId) {
      try {
        const token = localStorage.getItem('jwt_token')
        const response = await axios.post(
          'http://localhost:8000/api/v1/folder/delete',
          { id: folderId },
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )

        if (response.status === 200) {
          this.folders = this.folders.filter((folder) => folder.id !== folderId)
          toast.success('Dossier supprimé avec succès')
        } else {
          toast.error('Erreur lors de la suppression du dossier')
        }
      } catch (error) {
        console.error('Erreur lors de la suppression du dossier:', error)
      }
    },

    // Supprimer un fichier
    async removeFile(fileId) {
      try {
        const token = localStorage.getItem('jwt_token')
        const response = await axios.post(
          'http://localhost:8000/api/v1/file/delete',
          { id: fileId },
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )

        if (response.status === 200) {
          this.files = this.files.filter((file) => file.id !== fileId)
          this.folders.forEach((folder) => {
            folder.documents = folder.documents.filter((doc) => doc.id !== fileId)
          })
          toast.success('Fichier supprimé avec succès')
        } else {
          toast.error('Erreur lors de la suppression du fichier')
        }
      } catch (error) {
        console.error('Erreur lors de la suppression du fichier:', error)
      }
    },

    // Télécharger un document
    async uploadDocument(file) {
      try {
        const token = localStorage.getItem('jwt_token')
        const formData = new FormData()
        formData.append('file', file)

        const response = await axios.post('http://localhost:8000/api/v1/file/upload', formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data'
          }
        })

        if (response.status === 201) {
          this.files.push(response.data.file)
          const folder = this.folders.find((folder) => folder.title === response.data.file.folder)
          if (folder) {
            folder.documents.push(response.data.file)
          } else {
            // Ajouter dans le dossier "Racine" si aucun dossier n'est spécifié
            this.folders
              .find((folder) => folder.title === 'Racine')
              .documents.push(response.data.file)
          }
          toast.success('Fichier téléchargé avec succès')
        } else {
          toast.error('Erreur lors du téléchargement du fichier')
        }
      } catch (error) {
        console.error('Erreur lors du téléchargement du fichier:', error)
      }
    },

    // Déplacer un fichier vers un autre dossier
    async moveFile(fileId, targetFolderId) {
      try {
        const token = localStorage.getItem('jwt_token')
        const response = await axios.post(
          'http://localhost:8000/api/v1/file/move',
          {
            fileId,
            targetFolderId
          },
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )

        if (response.status === 200) {
          const file = this.files.find((file) => file.id === fileId)
          if (file) {
            const sourceFolder = this.folders.find((folder) =>
              folder.documents.some((doc) => doc.id === fileId)
            )
            const targetFolder = this.getFolderById(targetFolderId)

            if (sourceFolder && targetFolder) {
              sourceFolder.documents = sourceFolder.documents.filter((doc) => doc.id !== fileId)
              targetFolder.documents.push(file)
              toast.success('Fichier déplacé avec succès')
            }
          }
        } else {
          toast.error('Erreur lors du déplacement du fichier')
        }
      } catch (error) {
        console.error('Erreur lors du déplacement du fichier:', error)
      }
    },

    getFolderById(folderId) {
      return this.folders.find((folder) => folder.id === folderId)
    },

    clearData() {
      this.folders = []
      this.files = []
      this.documentsLoaded = false
    }
  }
})
