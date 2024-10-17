import { defineStore } from 'pinia'
import axios from 'axios'
import { toast } from 'vue3-toastify'
import { useUserStore } from '@/stores/UserStore/UserStore'

export const useLoginStore = defineStore('loginStore', {
  state: () => ({
    isLoginPage: true,
    isLoading: false
  }),
  actions: {
    changeLoginPage() {
      this.isLoginPage = !this.isLoginPage
    },
    async login(data, router) {
      this.isLoading = true
      const userStore = useUserStore()

      try {
        const response = await axios.post('https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/auth/login', {
          email: data.email,
          mdp: data.password
        })

        if (response.status === 200 && response.data) {
          toast.success(response.data.message)

          const userData = response.data.user
          if (userData) {
            userStore.setUser(userData)
            localStorage.setItem('jwt_token', response.data.token)
            localStorage.setItem('expires_at', new Date().getTime() + response.data.expires_in * 1000)

            console.log(userData)

            if (userData.FirstLogin) {
              router.push('/first-login-change-password')
            } else if (userData.role === 'Administrateur') {
              router.push('/management')
            } else {
              router.push('/home')
            }
          } else {
            throw new Error('Données utilisateur manquantes dans la réponse')
          }
        } else {
          throw new Error('Réponse du serveur invalide')
        }
      } catch (error) {
        console.error('Erreur lors de la connexion:', error)
        if (error.response && error.response.data && error.response.data.message) {
          toast.error(error.response.data.message)
        } else {
          toast.error('Une erreur est survenue lors de la connexion')
        }
      } finally {
        this.isLoading = false
      }
    },
    async firstLoginChange(newPassword, router) {
      this.isLoading = true

      try {
        const token = localStorage.getItem('jwt_token')
        if (!token) {
          throw new Error('Token d\'authentification manquant')
        }

        const response = await axios.post(
          'https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/password/first-login-change',
          { newPassword },
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )

        if (response.status === 200) {
          toast.success('Mot de passe changé avec succès')

          const userStore = useUserStore()
          const userData = userStore.user
          if (userData && userData.role === 'Administrateur') {
            router.push('/management')
          } else {
            router.push('/home')
          }
        } else {
          throw new Error('Réponse du serveur invalide')
        }
      } catch (error) {
        console.error('Erreur lors du changement de mot de passe:', error)
        toast.error(error.message || 'Échec de la modification du mot de passe')
      } finally {
        this.isLoading = false
      }
    },
    logout(router) {
      const userStore = useUserStore()

      localStorage.removeItem('jwt_token')
      localStorage.removeItem('expires_at')
      userStore.clearUser()
      router.push('/').then(() => {
        window.location.reload()
      })
    }
  }
})
