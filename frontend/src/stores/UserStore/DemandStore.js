import axios from 'axios'
import { defineStore } from 'pinia'
import { toast } from 'vue3-toastify'

export const useDemandStore = defineStore('demandsStore', {
  state: () => {
    return {
      demandes: [],
      demandesToBeValidated: [],
      motifs: [],
      documentsByStatus: {},
      showLoader: true,
      demandesLoaded: false
    }
  },
  actions: {
    async loadDemandes() {
      this.showLoader = true
      try {
        const token = localStorage.getItem('jwt_token')
        const response = await axios.post(
          'https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/getDemandsByUser',
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )

        if (response.data && response.data.length > 0) {
          this.demandes = response.data
            .map((demande) => {
              // Convertir les chaînes séparées par des virgules en tableaux
              const titreJustificatifs = demande.TitreJustificatifs
                ? demande.TitreJustificatifs.split(',')
                : []
              const justificatifs = demande.Justificatifs ? demande.Justificatifs.split(',') : []
              const titreAttachementResponses = demande.TitreAttachementResponses
                ? demande.TitreAttachementResponses.split(',')
                : []
              const attachementResponses = demande.AttachementResponses
                ? demande.AttachementResponses.split(',')
                : []

              return {
                id: demande.Id,
                title: demande.Titre,
                description: demande.Description,
                dateRequest: new Date(demande.Date).toLocaleDateString(),
                status: demande.Status,
                motif: demande.Motif,
                dateResponse:
                  demande.Status === 'Approuvée' || demande.Status === 'Refusée'
                    ? new Date(demande.DateReponse).toLocaleDateString()
                    : '-',
                date: new Date(demande.Date),
                documentUrls: titreJustificatifs.map((title, index) => ({
                  url: justificatifs[index],
                  filename: title
                })),
                responseAttachement: titreAttachementResponses.map((title, index) => ({
                  url: attachementResponses[index],
                  filename: title
                })),
                documentsCount: justificatifs.length
              }
            })
            .sort((a, b) => b.date - a.date)

          this.updateDocumentsByStatus()
          this.demandesLoaded = true
        }
      } catch (error) {
        console.error('Failed to load demandes', error)
      } finally {
        this.showLoader = false
      }
    },
    async fetchMotifs() {
      try {
        const response = await axios.get('https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/motifs')
        this.motifs = response.data
      } catch (error) {
        console.error('Erreur lors de la récupération des motifs :', error)
        toast.error('Erreur lors de la récupération des motifs')
      }
    },
    async createMotif(title) {
      this.showLoader = true
      try {
        const response = await axios.post('https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/motifs', { title })
        this.motifs.push(response.data.data)
        toast.success('Motif créé avec succès')
      } catch (error) {
        console.error('Erreur lors de la création du motif:', error)
        toast.error('Erreur lors de la création du motif')
      } finally {
        this.showLoader = false
      }
    },
    async updateMotif(id, title) {
      this.showLoader = true
      try {
        await axios.patch(`https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/motifs/${id}`, { title })
        const index = this.motifs.findIndex((motif) => motif.id === id)
        if (index !== -1) {
          this.motifs[index].title = title
        }
        toast.success('Motif mis à jour avec succès')
      } catch (error) {
        console.error('Erreur lors de la mise à jour du motif:', error)
        toast.error('Erreur lors de la mise à jour du motif')
      } finally {
        this.showLoader = false
      }
    },
    async archiveMotif(id) {
      this.showLoader = true
      try {
        await axios.patch(`https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/motifs/${id}/archive`)
        this.motifs = this.motifs.filter((motif) => motif.id !== id)
        toast.success('Motif archivé avec succès')
      } catch (error) {
        console.error("Erreur lors de l'archivage du motif:", error)
        toast.error("Erreur lors de l'archivage du motif")
      } finally {
        this.showLoader = false
      }
    },
    async unarchiveMotif(id) {
      this.showLoader = true;
      try {
        const response = await axios.patch(`https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/motifs/${id}/unarchive`);
        this.motifs = this.motifs.map(motif => {
          if (motif.id === id) {
            return { ...motif, isArchived: 0 };
          }
          return motif;
        });
        toast.success('Motif désarchivé avec succès');
      } catch (error) {
        console.error('Erreur lors de la désarchivage du motif:', error);
        toast.error('Erreur lors de la désarchivage du motif');
      } finally {
        this.showLoader = false; // Arrêter le loader
      }
    },
    
    async loadDemandesToBeValidated(role) {
      this.showLoader = true
      try {
        const token = localStorage.getItem('jwt_token')
        const response = await axios.post(
          'https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/getDemandsToValidate',
          { role },
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )

        console.log(response.data)

        if (response.data && response.data.length > 0) {
          this.demandesToBeValidated = response.data
            .map((demande) => {
              const justificatifsArray = demande.Justificatifs
                ? demande.Justificatifs.split(',')
                : []
              const titreJustificatifsArray = demande.TitreJustificatifs
                ? demande.TitreJustificatifs.split(',')
                : []

              const documentUrls = justificatifsArray.map((url, index) => ({
                url,
                filename: titreJustificatifsArray[index] || 'Document'
              }))

              return {
                id: demande.Id,
                title: demande.Titre,
                demandeur: demande.Demandeur,
                description: demande.Description,
                dateRequest: new Date(demande.Date).toLocaleDateString(),
                status: demande.Status,
                motif: demande.Motif,
                dateResponse:
                  demande.Status === 'Approuvée' || demande.Status === 'Refusée'
                    ? new Date(demande.DateReponse).toLocaleDateString()
                    : '-',
                date: new Date(demande.Date),
                documentUrls: documentUrls,
                documentsCount: documentUrls.length
              }
            })
            .sort((a, b) => b.date - a.date)
          this.updateDocumentsByStatus()
        }
      } catch (error) {
        console.error('Failed to load demandes to be validated', error)
      } finally {
        this.showLoader = false
        this.demandesLoaded = true
      }
    },
    async createDemand(formData, uploadedFiles) {
      this.showLoader = true
      try {
        const token = localStorage.getItem('jwt_token')
        const formDataToSend = new FormData()
        formDataToSend.append('Titre', formData.motif)
        formDataToSend.append('Description', formData.description)
        formDataToSend.append('Status', 'Soumis')

        if (uploadedFiles && uploadedFiles.length > 0) {
          uploadedFiles.forEach((file, index) => {
            formDataToSend.append(`filesToUpload[${index}]`, file)
          })
        }

        const response = await axios.post(
          'https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/createDemand',
          formDataToSend,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'multipart/form-data'
            }
          }
        )

        if (response.status >= 200 && response.status < 300) {
          toast.success(response.data.message)
          // Rafraîchir les demandes après la création
          await this.loadDemandes()
        } else {
          toast.error(response.data.message)
        }
      } catch (error) {
        console.error('Erreur lors de la création de la demande :', error)
        toast.error('Erreur lors de la création de la demande. Veuillez réessayer.')
      } finally {
        this.showLoader = false
      }
    },

    async takeChargeRequest(requestId) {
      const token = localStorage.getItem('jwt_token')
      try {
        const response = await axios.patch(
          `https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/requests/${requestId}/takeCharge`,
          { status: 'En cours' },
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )
        this.demandesLoaded = false
        toast.success(response.data.message)
      } catch (error) {
        console.error('Failed to take charge of the request', error)
        toast.error('Erreur lors de la prise en charge')
      }
    },

    async approveRequest(requestId, message, files) {
      const token = localStorage.getItem('jwt_token')
      console.log('------------')
      console.log(files)
      try {
        const formData = new FormData()
        formData.append('message', message)
        if (files && files.length > 0) {
          for (let i = 0; i < files.length; i++) {
            formData.append(`file${i}`, files[i])
          }
        }
        const response = await axios.post(
          `https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/requests/${requestId}/approve`,
          formData,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'multipart/form-data'
            }
          }
        )
        toast.success(response.data.message)
      } catch (error) {
        console.error('Failed to approve request', error)
        toast.error('Erreur rencontrée')
      }
    },
    async rejectRequest(requestId, message) {
      const token = localStorage.getItem('jwt_token')
      try {
        const response = await axios.post(
          `https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/requests/${requestId}/reject`,
          { message },
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )
        toast.success(response.data.message)
      } catch (error) {
        console.error('Failed to reject request', error)
        toast.error(error.response.data.message)
      }
    },
    async updateRequest(updatedRequest) {
      const token = localStorage.getItem('jwt_token')
      try {
        const response = await axios.patch(
          `https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/requests/${updatedRequest.id}/update`,
          updatedRequest,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        )
        toast.success('Demande mise à jour avec succès')
        await this.loadDemandes()
      } catch (error) {
        console.error('Erreur lors de la mise à jour de la demande :', error)
        toast.error('Erreur lors de la mise à jour de la demande')
      }
    },
    clearDemandes() {
      this.demandes = []
      this.demandesToBeValidated = []
      this.demandesLoaded = false
    },
    getEnCoursCount() {
      return this.demandes.filter((demande) => demande.status === 'En cours').length
    },
    getAccepteCount() {
      return this.demandes.filter((demande) => demande.status === 'Approuvée').length
    },
    getRefuseCount() {
      return this.demandes.filter((demande) => demande.status === 'Refusée').length
    },
    updateDocumentsByStatus() {
      const statusCount = {
        'En cours': 0,
        Approuvée: 0,
        Refusée: 0
      }

      this.demandes.forEach((demande) => {
        if (statusCount[demande.status] !== undefined) {
          statusCount[demande.status]++
        }
      })

      this.documentsByStatus = statusCount
    }
  }
})
