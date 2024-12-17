// stores/authStore.js
import { ref, computed, nextTick } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import router from '../router'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token') || null)
  const user = ref(JSON.parse(localStorage.getItem('user')))
  const users = ref([])

  function fetchUser() {
    const storedUser = localStorage.getItem('user')
    if (storedUser) {
      user.value = JSON.parse(storedUser)
    } else {
      user.value = null
    }
  }

  async function fetchUsers() {
    try {
      const response = await axios.get(
        'http://localhost:8000/api/v1/auth/users',
        {
          headers: {
            Authorization: `Bearer ${token.value}`,
          },
        },
      )
      users.value = response.data
      console.log(users.value)
    } catch (error) {
      console.error('Erreur lors de la récupération des utilisateurs:', error)
      throw error
    }
  }

  // Setter pour le token, stocke également dans localStorage
  function setToken(newToken) {
    token.value = newToken
    localStorage.setItem('token', newToken)
    axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
  }

  // Supprime le token du store et de localStorage
  function clearToken() {
    token.value = null
    localStorage.removeItem('token')
    delete axios.defaults.headers.common['Authorization']
  }

  // Stocker l'utilisateur dans localStorage
  function setUser(newUser) {
    user.value = newUser
    localStorage.setItem('user', JSON.stringify(newUser))
  }

  // Login: Authentifie et stocke le token
  async function login(email, password) {
    console.log('login')
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    localStorage.removeItem('expiration')
    try {
      const response = await axios.post(
        'http://localhost:8000/api/v1/auth/login',
        {
          email: email,
          password: password,
        },
      )
      setToken(response.data.token)
      localStorage.setItem('expiration', response.data.expiration)
      setUser(response.data.user)
      user.value = response.data.user

      if (response.data.user.first_login == 'true') {
        router.push({ name: 'forgot-password' })
      } else {
        if (user.value.role == 'Director') {
          router.push('/dashboard')
        } else {
          router.push('/home')
        }
      }
    } catch (error) {
      console.error('Erreur de connexion:', error)
      throw error
    }
  }

  function logout() {
    clearToken()
    user.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('expiration')
    nextTick(() => {
      router.replace('/auth/login')
    })
  }

  const isAuthenticated = computed(() => !!token.value)

  async function createUser(userData) {
    try {
      const response = await axios.post(
        'http://localhost:8000/api/v1/auth/createUser',
        userData,
        {
          headers: {
            Authorization: `Bearer ${token.value}`,
          },
        },
      )
      await fetchUsers()
      return response.data.user
    } catch (error) {
      console.error("Erreur lors de la création de l'utilisateur:", error)
      throw error
    }
  }

  async function updateUser(updatedData) {
    console.log(updatedData)
    try {
      const response = await axios.put(
        'http://localhost:8000/api/v1/auth/updateUser',
        updatedData,
        {
          headers: {
            Authorization: `Bearer ${token.value}`,
          },
        },
      )
      console.log(response)
      return response.data.user
    } catch (error) {
      console.error('Erreur lors de la mise à jour du profil:', error)
      throw error
    }
  }

  async function modifyUser(updatedUser) {
    try {
      const response = await axios.put(
        `http://localhost:8000/api/v1/auth/modifyUser/${updatedUser.id}`,
        updatedUser,
        {
          headers: {
            Authorization: `Bearer ${token.value}`,
          },
        },
      )

      await fetchUsers()

      return response.data.user
    } catch (error) {
      console.error("Erreur lors de la modification de l'utilisateur:", error)
      throw error
    }
  }

  async function archiveUser(userId) {
    try {
      const response = await axios.patch(
        `http://localhost:8000/api/v1/auth/archiveUser/${userId}`,
        {},
        {
          headers: {
            Authorization: `Bearer ${token.value}`,
          },
        },
      )
    } catch (error) {
      console.error("Erreur lors de l'archivage de l'utilisateur:", error)
      throw error
    }
  }

  async function checkEmail(email) {
    try {
      const response = await axios.post(
        'http://localhost:8000/api/v1/auth/checkEmail',
        { email },
      )
    } catch (error) {
      console.error("Erreur lors de la vérification de l'email:", error)
      throw error
    }
  }

  async function verifyCode(email, code) {
    try {
      const response = await axios.post(
        'http://localhost:8000/api/v1/auth/verifyCode',
        { email, code },
      )
    } catch (error) {
      console.error('Erreur lors de la vérification du code:', error)
      throw error
    }
  }

  async function resetPassword(email, newPassword) {
    try {
      const response = await axios.post(
        'http://localhost:8000/api/v1/auth/resetPassword',
        { email: email, password: newPassword },
      )
      router.push('/home')
    } catch (error) {
      console.error(
        'Erreur lors de la réinitialisation du mot de passe:',
        error,
      )
      throw error
    }
  }

  return {
    token,
    user,
    users,
    isAuthenticated,
    fetchUser,
    fetchUsers,
    createUser,
    login,
    logout,
    updateUser,
    modifyUser,
    archiveUser,
    checkEmail,
    verifyCode,
    resetPassword,
  }
})
