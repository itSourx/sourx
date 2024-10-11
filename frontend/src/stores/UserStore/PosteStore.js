import { defineStore } from 'pinia'
import axios from 'axios'

export const usePosteStore = defineStore('posteStore', {
  state: () => ({
    postes: [],
    showLoader: false
  }),

  actions: {
    async fetchPostes() {
      this.showLoader = true
      try {
        const token = localStorage.getItem('jwt_token')
        const response = await axios.get('http://localhost:8000/api/v1/postes', {
          headers: {
            Authorization: `Bearer ${token}`
          }
        })
        this.postes = response.data
      } catch (error) {
        console.error('Erreur lors de la récupération des postes:', error)
      } finally {
        this.showLoader = false
      }
    },

    async createPoste(posteData) {
      this.showLoader = true
      try {
        const token = localStorage.getItem('jwt_token')
        const response = await axios.post('http://localhost:8000/api/v1/postes', posteData, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        })
        this.postes.push(response.data.data)
      } catch (error) {
        console.error('Erreur lors de la création du poste:', error)
      } finally {
        this.showLoader = false
      }
    },

    async updatePoste(id, posteData) {
      this.showLoader = true
      try {
        const token = localStorage.getItem('jwt_token')
        await axios.patch(`http://localhost:8000/api/v1/postes/${id}`, posteData, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        })
        const index = this.postes.findIndex((poste) => poste.id === id)
        if (index !== -1) {
          this.postes[index] = { ...this.postes[index], ...posteData }
        }
      } catch (error) {
        console.error('Erreur lors de la mise à jour du poste:', error)
      } finally {
        this.showLoader = false
      }
    },

    async archivePoste(id) {
      this.showLoader = true
      try {
        const token = localStorage.getItem('jwt_token')
        await axios.patch(
          `http://localhost:8000/api/v1/postes/${id}/archive`,
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )
        const index = this.postes.findIndex((poste) => poste.id === id)
        if (index !== -1) {
          // Mettre à jour le statut isArchived du poste
          this.postes[index].isArchived = 1
        }
      } catch (error) {
        console.error("Erreur lors de l'archivage du poste:", error)
      } finally {
        this.showLoader = false
      }
    },
    async unarchivePoste(id) {
      this.showLoader = true
      try {
        const token = localStorage.getItem('jwt_token')
        const response = await axios.patch(
          `http://localhost:8000/api/v1/postes/${id}/unarchive`,
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )
        const index = this.postes.findIndex((poste) => poste.id === id)
        if (index !== -1) {
          this.postes[index].isArchived = 0
        }
      } catch (error) {
        console.error('Erreur lors de la désarchivage du poste:', error)
      } finally {
        this.showLoader = false
      }
    }
  }
})
