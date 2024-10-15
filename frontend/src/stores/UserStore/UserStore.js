import { defineStore } from 'pinia'
import { useFolderStore } from './FolderStore'
import axios from 'axios'
import { toast } from 'vue3-toastify'

export const useLoginStore = defineStore('loginStore', {
  state: () => {
    return { isLoginPage: true }
  },
  actions: {
    changeLoginPage() {
      this.isLoginPage = !this.isLoginPage
    }
  }
})

export const useViewDisplayer = defineStore('viewDisplayer', {
  state: () => {
    return { currentView: 'Accueil' }
  },
  actions: {
    setCurrentPage(name) {
      this.currentView = name
    },
    getCurrentPage() {
      return this.currentView
    }
  }
})

export const useUserStore = defineStore('userStore', {
  state: () => {
    return {
      user: null,
      teams: [],
      employees: [],
      recentActivities: []
      /* validators: [] */
    }
  },
  actions: {
    getUser() {
      return this.user
    },
    async updateUserProfile(profileData) {
      const token = localStorage.getItem('jwt_token')

      try {
        const response = await axios.put(
          'https://sourxhrtest-a90509d4033e.herokuapp.com/pi/v1/auth/updateProfile',
          profileData,
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )

        if (response.status === 200) {

          this.user = response.data;
          toast.success(response.data.message)

          localStorage.removeItem('jwt_token')
          localStorage.removeItem('user_data')
          setTimeout(() => {
            window.location.href = '/'
          }, 3000) 
        } else {
          console.error('Failed to update profile:', response)
        }
      } catch (error) {
        console.error('Error updating profile:', error)
      }
    },
    setUser(userData) {
      this.user = userData
      localStorage.setItem('user_data', JSON.stringify(userData))
    },
    clearUser() {
      this.user = null
      localStorage.removeItem('user_data')
      localStorage.removeItem('jwt_token')
      localStorage.removeItem('expires_at')
    },
    async loadUserFromLocalStorage() {
      const token = localStorage.getItem('jwt_token')
      const expiresAt = localStorage.getItem('expires_at')
      const userData = localStorage.getItem('user_data')

      if (!token || !expiresAt || isNaN(parseInt(expiresAt))) {
        console.error('No valid token or expiration time found')
        return
      }

      const currentTime = new Date().getTime()

      if (currentTime > parseInt(expiresAt)) {
        console.error('Token has expired')
        this.clearUser()

        return
      }

      if (userData) {
        try {
          this.user = JSON.parse(userData)
        } catch (error) {
          console.error('Failed to parse user data from local storage', error)
        }
        return
      }

      try {
        const response = await axios.post('https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/getAuthUser', {
          token: token
        })

        if (response.status === 200) {
          const userData = response.data.user
          this.setUser(userData)
          const folderStore = useFolderStore()
          await folderStore.fetchFolderNames()
        }
      } catch (error) {
        console.error('Failed to load user', error)
      }
    },
    async fetchAllEmployees() {
      const token = localStorage.getItem('jwt_token')
      try {
        if (this.employees.length === 0) {
          const response = await axios.post(
            'https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/allemployees',
            {},
            {
              headers: {
                Authorization: `Bearer ${token}`
              }
            }
          )
          if (response.status === 200 && response.data.employees) {
            const employeesData = response.data.employees.filter((employee) => employee.EquipeIsArchived.includes(0)).map((employee) => ({
              id: employee.id,
              email: employee.email || null,
              name: employee.nom + ' ' + employee.prenom || null,
              telephone: employee.telephone || null,
              role: employee.role || null,
              Equipe: employee.Equipe || null,
              EquipeIsArchived: employee.EquipeIsArchived || null,
              photo: employee.photo ? employee.photo[0]?.url : null
            }))

            this.setEmployees(employeesData)
            /* if (this.user.role === 'Salarie') {
              this.validators = this.employees.filter((employee) =>
                employee.role === 'Manager' &&
                employee.Equipe.some((equipe) => this.user.Equipe.includes(equipe)) 
              );
              console.log(this.validators)
            } */
          } else {
            console.error('Failed to fetch employees:', response)
          }
        }
      } catch (error) {
        console.error(error)
      }
    },
    async fetchRecentActivities() {
      if (!this.user) {
        console.error('User not available to fetch activities')
        return
      }
      const token = localStorage.getItem('jwt_token')

      try {
        const response = await axios.get('http://127.0.0.1:8000/api/v1/recentActivities', {
          headers: {
            Authorization: `Bearer ${token}`
          }
        })
        if (response.status === 200) {
          await this.fetchAllEmployees()
          this.recentActivities = response.data
        }
      } catch (error) {
        console.error('Failed to fetch recent activities', error)
      }
    },
    setRecentActivities(activities) {
      this.recentActivities = activities
    },
    setEmployees(employees) {
      this.employees = employees
    },
    getEmployees() {
      return this.employees
    }
  }
})

// for selected teams and persons in SelectMultiple.vue

export const useSelectionRecipient = defineStore('selection', {
  state: () => {
    return {
      selectedTeams: [],
      selectedIndividuals: []
    }
  },
  actions: {
    setSelectedTeams(teams) {
      this.selectedTeams = teams
    },
    setSelectedIndividuals(individuals) {
      this.selectedIndividuals = individuals
    },
    getSelectedTeams() {
      return this.selectedTeams
    },
    getSelectedIndividuals() {
      return this.selectedIndividuals
    }
  }
})
