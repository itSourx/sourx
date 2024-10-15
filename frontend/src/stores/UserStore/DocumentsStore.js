import { defineStore } from 'pinia'
import axios from 'axios'
import { toast } from 'vue3-toastify'

export const useDocumentStore = defineStore('documentStore', {
  state: () => ({
    allDocuments: [],
    allFolders: [],
    documentsLoaded: false,
    documentsByMonth: {}
  }),
  actions: {
    async fetchDocuments(token) {
      try {
        const response = await axios.post('https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/getDocumentByUser', {}, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        })

        if (response.data) {
          // Traitez les documents
          console.log(response.data)
          const sortedDocuments = response.data.documents.sort(
            (a, b) => new Date(b.createdTime) - new Date(a.createdTime)
          )

          const senders = await Promise.all(
            sortedDocuments.map(async (document) => {
              return {
                name: document.creatorName,
                photo: document.creatorPhoto
              }
            })
          )

          this.allDocuments = await Promise.all(
            sortedDocuments.map(async (doc, index) => {
              const size = doc.size
              return {
                ...doc,
                creatorName: senders[index].name,
                creatorPhoto: senders[index].photo,
                size: size ? size : '--'
              }
            })
          )

          // Traitez les dossiers
          this.allFolders = response.data.folders.map((folder) => {
            return {
              id: folder.id,
              name: folder.name,
              creatorId: folder.creatorId,
              creatorName: folder.creatorName,
              creatorRole: folder.creatorRole
            }
          })

          this.documentsLoaded = true // Marquer les documents comme chargés
          this.calculateDocumentsByMonth()
        }
      } catch (error) {
        console.error(error)
        toast.error('Erreur lors du chargement des documents.')
      }
    },
    async sendDocument(currentUser, destinationFolder, selectedUsers, filesToUpload) {

      const token = localStorage.getItem('jwt_token')
      const formData = new FormData()
      formData.append('currentUser', JSON.stringify(currentUser))
      formData.append('destinationFolder', destinationFolder)
      //formData.append('selectedUsers', JSON.stringify(selectedUsers))
      selectedUsers.forEach((user, index) => {
        formData.append(`selectedUsers[${index}]`, user) // Envoyer chaque élément du tableau séparément
      })

      filesToUpload.forEach((file, index) => {
        formData.append(`filesToUpload[${index}]`, file)
      })

      console.log("//")
      console.log(formData)

      try {
        const response = await axios.post(
          'https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/sendDocumentByUser',
          formData,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'multipart/form-data'
            }
          }
        )

        if (response.status === 200) {
          toast.success(response.data.message)
        } else {
          toast.error(response.data.message)
        }
      } catch (error) {
        console.error("Erreur lors de l'envoi des documents:", error)
        toast.error("Erreur lors de l'envoi des documents")
      }
    },

    getDocuments() {
      return this.allDocuments
    },
    areDocumentsLoaded() {
      return this.documentsLoaded
    },
    async refreshDocuments(token) {
      await this.fetchDocuments(token)
    },
    calculateDocumentsByMonth() {
      const monthCounts = {}

      this.allDocuments.forEach((document) => {
        const month = new Date(document.createdTime).toLocaleString('default', { month: 'long' })
        monthCounts[month] = (monthCounts[month] || 0) + 1
      })

      this.documentsByMonth = monthCounts
    },
    async deleteDocument(documentId) {
      try {
        const response = await axios.post(
          'https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/document/delete',
          { id: documentId },
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('jwt_token')}`
            }
          }
        )
        if (response.status === 200) {
          // Supprimer le document du state
          this.allDocuments = this.allDocuments.filter((doc) => doc.id !== documentId)
          this.calculateDocumentsByMonth() // Recalculer les documents par mois après suppression
          toast.success(response.data.message)
        } else {
          console.error('Erreur lors de la suppression du document:', response.data)
          toast.error(response.data.message)
        }
      } catch (error) {
        console.error('Erreur lors de la suppression du document:', error)
      }
    },
    async moveDocument(documentId, folderDestinationName) {
      try {
        const response = await axios.post(
          `https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/document/move/${documentId}/${folderDestinationName}`,
          {},
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('jwt_token')}`
            }
          }
        )
        if (response.status === 200) {
          toast.success(response.data.message)
        }
      } catch (error) {
        toast.error(error.response.data.message)
        console.error('Erreur lors du déplacement du document', error)
      }
    }
  }
})
