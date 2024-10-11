<template>
    <div class="flex h-screen">
        <div class="hidden md:block md:w-1/2 bg-cover bg-center bg-img-login relative"></div>
        <div class="w-full md:w-1/2 flex items-center justify-center p-6 md:p-10">
            <LoginView v-if="loginState.isLoginPage"></LoginView>
            <ForgotPassword v-else></ForgotPassword>
        </div>
    </div>
</template>

<script setup>
import { useLoginStore } from '@/stores/AuthentificationStore/AuthStore'

import LoginView from '@/views/Auth/LoginView.vue'
import ForgotPassword from '@/views/Auth/ForgotPassword.vue'

const loginState = useLoginStore()
</script>
