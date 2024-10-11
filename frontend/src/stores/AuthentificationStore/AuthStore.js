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
        const response = await axios.post('http://localhost:8000/api/v1/auth/login', {
          email: data.email,
          mdp: data.password
        })

        if (response.status === 200) {
          toast.success(response.data.message)

          const userData = response.data.user
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
        }
      } catch (error) {
        toast.error(error.response.data.message)
        console.error(error)
      } finally {
        this.isLoading = false
      }
    },
    async firstLoginChange(newPassword, router) {
      this.isLoading = true

      try {
        const token = localStorage.getItem('jwt_token')
        const response = await axios.post(
          'http://localhost:8000/api/v1/password/first-login-change',
          {
            newPassword: newPassword
          },
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
          if (userData.role === 'Administrateur') {
            router.push('/management')
          } else {
            router.push('/home')
          }
        }
      } catch (error) {
        console.error(error)
        toast.error('Échec de la modification du mot de passe')
      } finally {
        this.isLoading = false
      }
    },
    logout(router) {
      const userStore = useUserStore()

      // Réinitialiser les informations d'authentification et rediriger l'utilisateur
      localStorage.removeItem('jwt_token')
      userStore.clearUser()
      router.push('/').then(() => {
        window.location.reload()
      })
    }
  }
})
