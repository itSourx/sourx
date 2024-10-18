<template>
  <div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Mon compte</h1>
    <p class="mb-8">Récapitulatif de mes informations</p>

    <div class="bg-white p-6 rounded-lg shadow-md">
      <div class="flex items-center mb-6">
        <div
          class="w-40 h-40 border border-gray-light rounded-full mr-6 flex items-center justify-center overflow-hidden bg-gray-100">
          <img :src="profilePicture" alt="Photo de profil" class="w-full h-full object-cover"
            v-if="profilePicture !== ''" />
          <span v-else class="text-gray-light">Photo de profil</span>
        </div>
        <div class="flex items-center">
          <input type="file" @change="uploadProfilePicture" class="hidden" id="fileInput" />
          <label for="fileInput" class="bg-green text-white py-2 px-4 rounded-md mr-2 cursor-pointer">Télécharger une
            nouvelle photo</label>
          <button @click="deleteProfilePicture" class="bg-red text-white py-2 px-4 rounded-md">
            Supprimer
          </button>
        </div>
      </div>

      <!-- Validation des erreurs -->
      <div v-if="validationErrors.length" class="mb-4 text-red">
        <ul>
          <li v-for="(error, index) in validationErrors" :key="index">{{ error }}</li>
        </ul>
      </div>

      <div class="mb-6">
        <label class="block text-gray-medium">Nom complet</label>
        <div class="flex space-x-4 mt-2">
          <div class="relative flex items-center">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
              <User class="text-gray-light w-5 h-5" />
            </span>
            <input v-model="firstName" type="text" placeholder="Prénom"
              class="pl-12 mt-1 p-3 py-5 w-full rounded-md text-sm border border-gray-light" />
          </div>
          <div class="relative flex items-center">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
              <User class="text-gray-light w-5 h-5" />
            </span>
            <input v-model="lastName" type="text" placeholder="Nom"
              class="pl-12 mt-1 p-3 py-5 w-full rounded-md text-sm border border-gray-light" />
          </div>
        </div>
      </div>

      <div class="mb-6">
        <label class="block text-gray-medium">Email</label>
        <div class="relative flex items-center mt-2">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
            <Mail class="text-gray-light w-5 h-5" />
          </span>
          <input v-model="email" type="email" placeholder="Email" disabled
            class="pl-12 mt-1 p-3 py-5 w-full rounded-md text-sm border border-gray-light" />
        </div>
      </div>

      <div class="mb-6">
        <label class="block text-gray-medium">Téléphone</label>
        <div class="relative flex items-center mt-2">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
            <PhoneCall class="text-gray-light w-5 h-5" />
          </span>
          <input v-model="telephone" type="tel" placeholder="Téléphone"
            class="pl-12 mt-1 p-3 py-5 w-full rounded-md text-sm border border-gray-light" />
        </div>
      </div>

      <!-- Masquer ces champs si l'utilisateur est administrateur -->
      <div v-if="user.isAdmin === 0" class="mb-6">
        <label class="block text-gray-medium">Rôle</label>
        <div class="relative flex items-center mt-2">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
            <Users class="text-gray-light w-5 h-5" />
          </span>
          <input v-model="role" type="text" placeholder="Rôle"
            class="pl-12 mt-1 p-3 py-5 w-full rounded-md text-sm border border-gray-light" :disabled="!user.isAdmin" />
        </div>
      </div>

      <div v-if="user.isAdmin === 0" class="mb-6">
        <label class="block text-gray-medium">Équipe</label>
        <div class="relative flex items-center mt-2">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
            <Users class="text-gray-light w-5 h-5" />
          </span>
          <input v-model="equipe" type="text" placeholder="Équipe"
            class="pl-12 mt-1 p-3 py-5 w-full rounded-md text-sm border border-gray-light" :disabled="!user.isAdmin" />
        </div>
      </div>

      <div class="mb-6">
        <label class="block text-gray-medium">Mot de passe</label>
        <div class="flex space-x-4 mt-2">
          <div class="relative flex items-center w-full">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
              <LockKeyhole class="text-gray-light w-5 h-5" />
            </span>
            <input v-model="currentPassword" type="password" placeholder="Mot de passe actuel"
              class="pl-12 mt-1 p-3 py-5 w-full rounded-md text-sm border border-gray-light" />
          </div>
          <div class="relative flex items-center w-full">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
              <LockKeyhole class="text-gray-light w-5 h-5" />
            </span>
            <input v-model="newPassword" type="password" placeholder="Nouveau mot de passe"
              class="pl-12 mt-1 p-3 py-5 w-full rounded-md text-sm border border-gray-light" />
          </div>
        </div>
      </div>

      <div class="flex justify-between">
        <button @click="saveSettings" class="bg-green text-white py-2 px-4 rounded-md">
          Enregistrer les modifications
        </button>
        <button @click="logout" class="bg-red text-white py-2 px-4 rounded-md">Déconnexion</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useUserStore } from '@/stores/UserStore/UserStore'
import defaultProfilePicture from '@/assets/user-icon.svg'
import router from '@/router'

const userStore = useUserStore()
const user = userStore.getUser()

const profilePicture = ref(user?.photo || defaultProfilePicture)
const firstName = ref(user?.prenom || '')
const lastName = ref(user?.nom || '')
const email = ref(user?.email || '')
const telephone = ref(user?.telephone || '')
const role = ref(user?.role || '')
const equipe = ref(Array.isArray(user.Equipe) ? user.Equipe : Object.values(user.Equipe) || '')
const currentPassword = ref('')
const newPassword = ref('')
const validationErrors = ref([])

const validateForm = () => {
  validationErrors.value = []

  if (!firstName.value.trim()) {
    validationErrors.value.push("Le prénom ne peut pas être vide.")
  }

  if (!lastName.value.trim()) {
    validationErrors.value.push("Le nom ne peut pas être vide.")
  }

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!email.value.trim()) {
    validationErrors.value.push("L'email ne peut pas être vide.")
  } else if (!emailPattern.test(email.value)) {
    validationErrors.value.push("L'email n'est pas valide.")
  }

  const phonePattern = /^\+?[0-9]{7,15}$/
  if (!telephone.value.trim()) {
    validationErrors.value.push("Le téléphone ne peut pas être vide.")
  } else if (!phonePattern.test(telephone.value)) {
    validationErrors.value.push("Le téléphone doit contenir uniquement des chiffres et peut inclure un indicatif.")
  }

  if (newPassword.value && currentPassword.value === newPassword.value) {
    validationErrors.value.push("Le nouveau mot de passe ne peut pas être identique à l'ancien.")
  } else if (newPassword.value && newPassword.value.length < 8) {
    validationErrors.value.push("Le nouveau mot de passe doit contenir au moins 8 caractères.")
  }

  return validationErrors.value.length === 0
}


const uploadProfilePicture = (event) => {
  const file = event.target.files[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      profilePicture.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const deleteProfilePicture = () => {
  profilePicture.value = defaultProfilePicture
}

const saveSettings = async () => {
  if (!validateForm()) {
    return
  }

  const profileData = {
    prenom: firstName.value,
    nom: lastName.value,
    email: email.value,
    telephone: telephone.value,
    photo: profilePicture.value,
    current_password: currentPassword.value,
    new_password: newPassword.value
  }

  try {
    await userStore.updateUserProfile(profileData)
    alert('Votre profil a été mis à jour avec succès. Veuillez vous reconnecter.')
  } catch (error) {
    console.error('Failed to save settings:', error)
  }
}

const logout = () => {
  localStorage.removeItem('jwt_token')
  userStore.clearUser()
  router.push('/')
}

onMounted(async () => {
  await userStore.loadUserFromLocalStorage()
})
</script>
