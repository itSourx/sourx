<template>
  <div class="w-full max-w-md">
    <img src="@/assets/illustration-login.svg" alt="Illustration" class="mx-auto mb-6 h-36" />
    <h1 class="text-2xl my-8">Se connecter</h1>

    <form @submit.prevent="handleLogin" class="text-night">
      
      <div class="mb-4 relative">
        <label for="email" class="block text-sm font-medium">Email * </label>
        <div class="relative flex items-center">
          <span class="absolute inset-y-0 left-0 pl-3 pt-1 flex items-center">
            <Mail class="text-gray-light w-5 h-5" />
          </span>
          <input v-model="data.email" type="email" id="email" name="email"
            class="pl-12 mt-1 p-3 py-5 w-full rounded-md text-xs border border-gray-light" />
        </div>
      </div>

      <div class="mb-4 relative">
        <label for="password" class="block text-sm font-medium">Mot de passe * </label>
        <div class="relative flex items-center">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
            <LockKeyhole class="text-gray-light w-5 h-5" />
          </span>
          <input v-model="data.password" :type="isPasswordVisible ? 'text' : 'password'" id="password" name="password"
            class="input-password pl-12 pr-12 mt-1 p-3 py-5 w-full rounded-md text-xs border border-gray-light" />
          <span class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer"
            @click="togglePasswordVisibility">
            <component :is="isPasswordVisible ? 'EyeOffIcon' : 'Eye'" class="text-gray-light w-5 h-5" />
          </span>
        </div>
      </div>

      <small v-if="loginError" class="text-red">La connexion a échoué</small>
      <div class="flex items-center justify-between">
        <small><!-- Keep me logged --></small>
        <small class="text-right hover:underline cursor-pointer" @click="displayForgotPassword()">Mot de passe oublié</small>
      </div>

      <LoaderComponent v-if="loginStore.isLoading"></LoaderComponent>

      <button type="submit"
        class="bg-oxford-blue text-white p-3 py-5 w-full rounded-md mt-5 text-xs hover:bg-zaffre hover:shadow-lg transform transition duration-200 ease-in-out">
        SE connecter
      </button>
    
    </form>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useLoginStore } from '@/stores/AuthentificationStore/AuthStore'

const data = reactive({
  email: '',
  password: ''
})
const router = useRouter()
const isPasswordVisible = ref(false)
const loginStore = useLoginStore()

const handleLogin = async () => {
  await loginStore.login(data, router)
}

const togglePasswordVisibility = () => {
  isPasswordVisible.value = !isPasswordVisible.value
}

const displayForgotPassword = () => {
  loginStore.changeLoginPage()
}
</script>

<style scoped>
.input-password {
  font-size: 1rem;
}
</style>
