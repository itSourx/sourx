import { defineStore } from 'pinia'
import axios from 'axios'
import { toast } from 'vue3-toastify'

export const useUserManagementStore = defineStore('userManagementStore', {
  state: () => ({
    users: [],
    teams: [],
    loading: false,
    initialized: false 
  }),
  actions: {
    async initialize() {
      if (this.initialized == false) {
        await this.fetchAllUsers()
        this.initialized = true
      }
    },

    // Récupérer tous les utilisateurs de la plateforme
    async fetchAllUsers() {
      if(this.initialized == true) return;
      
      this.loading = true
      const token = localStorage.getItem('jwt_token')

      try {
        const response = await axios.post(
          'https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/allemployees',
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )

        console.log(response)

        if (response.status === 200 && response.data.employees) {
          this.users = response.data.employees
            .filter((employee) => employee) // Filtrer les objets null ou undefined
            .map((employee) => ({
              id: employee.id,
              email: employee.email || null,
              nom: employee.nom || null,
              prenom: employee.prenom || null,
              telephone: employee.telephone || null,
              role: employee.role || 'Non défini',
              poste: employee.poste,
              Equipe: employee.Equipe || [],
              photo: employee.photo ? employee.photo[0]?.url : null,
              isArchived: employee.isArchived,
              EquipeIsArchived: employee.EquipeIsArchived
            }))

          this.generateTeams()
          this.initialized = true

        } else {
          toast.error('Échec de la récupération des utilisateurs')
          console.error('Échec de la récupération des utilisateurs:', response)
        }
      } catch (error) {
        toast.error('Erreur lors de la récupération des utilisateurs')
        this.initialized = false
        console.error('Erreur lors de la récupération des utilisateurs:', error)
      } finally {
        this.loading = false
      }
    },

    generateTeams() {
      const teamMap = new Map()

      this.users.forEach((user) => {
        const equipes = typeof user.Equipe === 'string' ? [user.Equipe] : user.Equipe || []

        equipes.forEach((equipe) => {
          if (!teamMap.has(equipe)) {
            teamMap.set(equipe, { name: equipe, isArchived: user.EquipeIsArchived, members: [] })
          }
          teamMap.get(equipe).members.push(user)
        })
      })

      this.teams = Array.from(teamMap.values())
    },

    // Créer un nouvel utilisateur
    async createUser(newUser) {
      const token = localStorage.getItem('jwt_token')

      try {
        const response = await axios.post('https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/createUser', newUser, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        })

        if (response.status === 200) {
          // Recharger tous les utilisateurs après la création pour éviter les incohérences
          await this.fetchAllUsers()
          toast.success(response.data.message)
        } else {
          toast.error(response.data.message)
          console.error("Échec de la création de l'utilisateur:", response)
        }
      } catch (error) {
        toast.error("Erreur lors de la création de l'utilisateur")
        console.error("Erreur lors de la création de l'utilisateur:", error)
      }
    },

    // Supprimer un utilisateur
    async deleteUser(userId) {
      const token = localStorage.getItem('jwt_token')

      try {
        const response = await axios.delete(`https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/deleteUser/${userId}`, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        })

        if (response.status === 200) {
          this.users = this.users.filter((user) => user.id !== userId)
          toast.success(response.data.message)
        } else {
          toast.error("Échec de la suppression de l'utilisateur")
          console.error("Échec de la suppression de l'utilisateur:", response)
        }
      } catch (error) {
        toast.error("Erreur lors de la suppression de l'utilisateur")
        console.error("Erreur lors de la suppression de l'utilisateur:", error)
      }
    },

    // Mettre à jour les informations d'un utilisateur
    async updateUser(userId, updatedData) {
      const token = localStorage.getItem('jwt_token')

      try {
        const response = await axios.put(
          `https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/updateUser/${userId}`,
          updatedData,
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )

        if (response.status === 200) {
          const index = this.users.findIndex((user) => user.id === userId)
          if (index !== -1) {
            this.users[index] = { ...this.users[index], ...response.data.user }
            toast.success('Utilisateur mis à jour avec succès')
          }
        } else {
          toast.error("Échec de la mise à jour de l'utilisateur")
          console.error("Échec de la mise à jour de l'utilisateur:", response)
        }
      } catch (error) {
        toast.error("Erreur lors de la mise à jour de l'utilisateur")
        console.error("Erreur lors de la mise à jour de l'utilisateur:", error)
      }
    },

    async toggleArchiveStatus(userId, updatedData) {
      const token = localStorage.getItem('jwt_token')

      try {
        const response = await axios.put(
          `https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/toggleArchiveStatus/${userId}`,
          updatedData,
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )

        if (response.status === 200) {
            toast.success('Utilisateur mis à jour avec succès')
        } else {
          toast.error("Échec de la mise à jour de l'utilisateur")
        }
      } catch (error) {
        toast.error("Erreur lors de la mise à jour de l'utilisateur")
        console.error("Erreur lors de la mise à jour de l'utilisateur:", error)
      }
    },

    async toggleTeamArchiveStatus(teamName, updatedData) {
      const token = localStorage.getItem('jwt_token')

      try {
        const response = await axios.put(
          `https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/team/toggleTeamArchiveStatus/${teamName}`,
          updatedData,
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )

        if (response.status === 200) {
            toast.success('Utilisateur mis à jour avec succès')
        } else {
          toast.error("Échec de la mise à jour de l'utilisateur")
        }
      } catch (error) {
        toast.error("Erreur lors de la mise à jour de l'utilisateur")
        console.error("Erreur lors de la mise à jour de l'utilisateur:", error)
      }
    },

    

    async createTeam(newTeam) {
      const token = localStorage.getItem('jwt_token')

      try {
        const response = await axios.post('https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/createTeam', newTeam, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        })

        if (response.status === 200) {
          const teamMembers = response.data.team.fields.Employee.map((memberId) => {
            return this.users.find((user) => user.id === memberId)
          })

          const formattedTeam = {
            name: response.data.team.fields.Nom,
            members: teamMembers
          }

          this.teams.push(formattedTeam)
          toast.success('Équipe créée avec succès')
        } else {
          toast.error("Échec de la création de l'équipe")
          console.error("Échec de la création de l'équipe:", response)
        }
      } catch (error) {
        toast.error("Erreur lors de la création de l'équipe")
        console.error("Erreur lors de la création de l'équipe:", error)
      }
    },

    // Mettre à jour une équipe
    async updateTeam(oldTeamName, updatedTeam) {
      const token = localStorage.getItem('jwt_token')

      try {
        const response = await axios.put(
          `https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/updateTeam/${oldTeamName}`,
          updatedTeam,
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )

        if (response.status === 200) {
          const index = this.teams.findIndex((team) => team.name === oldTeamName)
          if (index !== -1) {
            // Met à jour le nom de l'équipe et les autres informations
            this.teams[index] = {
              ...this.teams[index],
              name: updatedTeam.name, // Nouveau nom de l'équipe
              members: updatedTeam.members
            }
            toast.success('Équipe mise à jour avec succès')
          }
        } else {
          toast.error("Échec de la mise à jour de l'équipe")
          console.error("Échec de la mise à jour de l'équipe:", response)
        }
      } catch (error) {
        toast.error("Erreur lors de la mise à jour de l'équipe")
        console.error("Erreur lors de la mise à jour de l'équipe:", error)
      }
    },

    async deleteTeam(teamName) {
      const token = localStorage.getItem('jwt_token')

      try {
        const response = await axios.delete(`https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/deleteTeam/${teamName}`, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        })

        if (response.status === 200) {
          this.teams = this.teams.filter((team) => team.name !== teamName)
          toast.success('Équipe supprimée avec succès')
        } else {
          toast.error("Échec de la suppression de l'équipe")
          console.error("Échec de la suppression de l'équipe:", response)
        }
      } catch (error) {
        toast.error("Erreur lors de la suppression de l'équipe")
        console.error("Erreur lors de la suppression de l'équipe:", error)
      }
    }
  }
})
