<template>
  <div class="w-full max-w-md">
    <h1 class="text-2xl my-8">Forgot Password</h1>

    <form @submit.prevent="handleFormSubmit" class="text-night">
      <!-- Étape 1: Saisie de l'email -->
      <div v-if="currentStep === 1" class="relative">
        <label for="email" class="block text-sm font-medium">Email *</label>
        <div class="relative flex items-center">
          <span class="absolute inset-y-0 left-0 pl-3 pt-1 flex items-center">
            <Mail class="text-gray-light w-5 h-5" />
          </span>
          <input v-model="email" placeholder="Enter your email address" type="email" id="email" name="email"
            class="pl-12 mt-1 p-3 py-5 w-full rounded-md text-xs border border-gray-light" />
        </div>
      </div>

      <!-- Étape 2: Saisie du code -->
      <div v-if="currentStep === 2" class="relative">
        <label for="code" class="block text-sm font-medium">Verification Code *</label>
        <div class="relative flex items-center">
          <span class="absolute inset-y-0 left-0 pl-3 pt-1 flex items-center">
            <LockKeyhole class="text-gray-light w-5 h-5" />
          </span>
          <input v-model="code" placeholder="Enter the code sent to your email" type="text" id="code" name="code"
            class="pl-12 mt-1 p-3 py-5 w-full rounded-md text-xs border border-gray-light" />
        </div>
      </div>

      <!-- Étape 3: Saisie du nouveau mot de passe -->
      <div v-if="currentStep === 3" class="relative">
        <label for="password" class="block text-sm font-medium">New Password *</label>
        <div class="relative flex items-center">
          <span class="absolute inset-y-0 left-0 pl-3 pt-1 flex items-center">
            <LockKeyhole class="text-gray-light w-5 h-5" />
          </span>
          <input v-model="newPassword" placeholder="Enter your new password" type="password" id="password"
            name="password" class="pl-12 mt-1 p-3 py-5 w-full rounded-md text-xs border border-gray-light" />
        </div>
        <label for="confirmPassword" class="block text-sm font-medium mt-4">Confirm Password *</label>
        <div class="relative flex items-center">
          <span class="absolute inset-y-0 left-0 pl-3 pt-1 flex items-center">
            <LockKeyhole class="text-gray-light w-5 h-5" />
          </span>
          <input v-model="confirmPassword" placeholder="Confirm your new password" type="password" id="confirmPassword"
            name="confirmPassword" class="pl-12 mt-1 p-3 py-5 w-full rounded-md text-xs border border-gray-light" />
        </div>
      </div>

      <LoaderComponent v-if="isLoading"></LoaderComponent>

      <button type="submit"
        class="bg-oxford-blue text-white p-3 py-5 mb-4 w-full rounded-md mt-5 text-xs hover:bg-zaffre hover:shadow-lg transform transition duration-200 ease-in-out">
        {{ currentStep === 1 ? 'Valider' : currentStep === 2 ? 'Verify Code' : 'Reset Password' }}
      </button>

      <div class="flex items-center justify-between">
        <small></small>
        <small class="text-right hover:underline cursor-pointer" @click="displayLogin()">Se connecter</small>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useLoginStore } from '@/stores/UserStore/UserStore'
import LoaderComponent from '@/components/LoaderComponent.vue'
import axios from 'axios'
import { ref } from 'vue'
import { toast } from 'vue3-toastify'

const loginState = useLoginStore()
const email = ref('')
const code = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const isLoading = ref(false)
const currentStep = ref(1)

const displayLogin = () => {
  loginState.changeLoginPage()
}

const handleFormSubmit = async () => {
  if (currentStep.value === 1) {
    await handleEmailSubmit()
  } else if (currentStep.value === 2) {
    await handleCodeSubmit()
  } else if (currentStep.value === 3) {
    await handlePasswordReset()
  }
}

const handleEmailSubmit = async () => {
  isLoading.value = true
  try {
    const response = await axios.post('https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/password/reset', {
      email: email.value
    })

    if (response.status === 200) {
      currentStep.value = 2
    }
  } catch (error) {
    console.error(error)
    toast.error("Erreur de vérification du mail");
  } finally {
    isLoading.value = false
  }
}

const handleCodeSubmit = async () => {
  isLoading.value = true
  try {
    const response = await axios.post('https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/password/verify', {
      email: email.value,
      code: code.value
    })

    if (response.status === 200) {
      currentStep.value = 3
    }
  } catch (error) {
    console.error(error)
    toast.error("Erreur de vérification du code");
  } finally {
    isLoading.value = false
  }
}

const handlePasswordReset = async () => {
  if (newPassword.value !== confirmPassword.value) {
    toast.error("Les mots de passe ne correspondent pas");
    return;
  }

  isLoading.value = true
  try {
    const response = await axios.post('https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/password/confirm', {
      email: email.value,
      newPassword: newPassword.value
    })

    if (response.status === 200) {
      toast.success("Mot de passe réinitialisé avec succès");
      displayLogin()
    }
  } catch (error) {
    console.error(error)
    toast.error("Erreur lors de la réinitialisation du mot de passe");
  } finally {
    isLoading.value = false
  }
}
</script>
