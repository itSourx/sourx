<template>
  <nav v-if="user" class="bg-blue py-4 px-8 relative">
    <div class="container mx-auto flex items-center justify-between">
      <!-- Left Section: Logo and Links -->
      <div class="flex items-center space-x-8">
        <h1 class="text-yellow font-bold text-lg">SOURX DOCS</h1>
        <router-link to="/home" v-if="!user.isAdmin" class="nav-link hidden lg:inline-block">Accueil</router-link>
        <!-- Menu Links visible sur écrans larges -->
        <div v-if="!mobileMenuOpen" class="hidden lg:flex lg:space-x-8 items-center text-white mx-7">
          <router-link to="/home/documents" v-if="!user.isAdmin" class="nav-link">Mes documents</router-link>
          <router-link to="/home/requests" v-if="!user.isAdmin && user.role === 'Salarie'" class="nav-link">Mes
            demandes</router-link>
          <router-link to="/home/myteam" v-if="user.isAdmin === 0 && user.role !== 'Salarie'" class="nav-link">Mon
            équipe</router-link>
          <router-link to="/management" v-if="user.isAdmin" class="nav-link">Dashboard Admin</router-link>
          <router-link to="/management/documents" v-if="user.isAdmin" class="nav-link">Documents</router-link>
          <router-link to="/home/requests" v-if="user.role !== 'Salarie'" class="nav-link">Demandes</router-link>
          <router-link to="/management/administration" v-if="user.isAdmin" class="nav-link">Paramètres</router-link>
        </div>
      </div>

      <!-- Right Section (Hamburger & User Profile for Desktop) -->
      <div class="flex items-center space-x-4">
        <!-- Hamburger Icon (Small screens) -->
        <button @click="toggleMobileMenu" class="lg:hidden text-white focus:outline-none">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <!-- User Profile (Desktop only) -->
        <div v-if="user" class="hidden lg:flex items-center space-x-4">
          <img v-if="user.photo" :src="user.photo" alt="User Photo"
            class="w-10 h-10 rounded-full object-cover cursor-pointer" />
          <div v-else
            class="w-10 h-10 rounded-full bg-gray-medium text-white flex items-center justify-center cursor-pointer font-semibold">
            {{ getUserInitials(user.nom) }}
          </div>
          <span v-if="user.role === 'Salarie'" class="text-white">{{ user.prenom }}</span>
          <UserRoleTag v-else :role="user.role" />
        </div>
      </div>

      <!-- Mobile Menu -->
      <transition name="slide-fade">
        <div v-if="mobileMenuOpen"
          class="absolute top-16 left-0 w-full z-20 lg:hidden bg-oxford-blue text-white py-4 space-y-2">
          <router-link to="/home/documents" v-if="!user.isAdmin" class="nav-link block px-6">Mes documents</router-link>
          <router-link to="/home/requests" v-if="!user.isAdmin && user.role === 'Salarie'"
            class="nav-link block px-6">Mes demandes</router-link>
          <router-link to="/home/myteam" v-if="user.isAdmin === 0 && user.role !== 'Salarie'"
            class="nav-link block px-6">Mon équipe</router-link>
          <router-link to="/management" v-if="user.isAdmin" class="nav-link block px-6">Dashboard Admin</router-link>
          <router-link to="/management/documents" v-if="user.isAdmin"
            class="nav-link block px-6">Documents</router-link>
          <router-link to="/home/requests" v-if="user.role !== 'Salarie'"
            class="nav-link block px-6">Demandes</router-link>
          <router-link to="/management/administration" v-if="user.isAdmin"
            class="nav-link block px-6">Paramètres</router-link>

          <!-- User Profile Section in Mobile Menu -->
          <div class="flex items-center px-6 space-x-4 mt-4">
            <img v-if="user.photo" :src="user.photo" alt="User Photo" class="w-10 h-10 rounded-full object-cover" />
            <div v-else
              class="w-10 h-10 rounded-full bg-gray-medium text-white flex items-center justify-center font-semibold">
              {{ getUserInitials(user.nom) }}
            </div>
            <div class="text-white">
              <span>{{ user.prenom }}</span>
              <UserRoleTag :role="user.role" />
            </div>
          </div>
        </div>
      </transition>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue'
import { useUserStore } from '@/stores/UserStore/UserStore.js'
import { useLoginStore } from '@/stores/AuthentificationStore/AuthStore'
import { useRouter } from 'vue-router'
import UserRoleTag from '@/components/UserRole.vue'
import { getUserInitials } from '@/utils'

const userStore = useUserStore()
const loginStore = useLoginStore()
const user = userStore.getUser()
const router = useRouter()

const dropdownOpen = ref(false)
const mobileMenuOpen = ref(false)

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}
</script>

<style scoped>
.nav-link {
  @apply text-white text-sm font-medium hover:font-semibold transition duration-200 ease-in-out;
}

/* Animation for Slide Dropdown */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease-in-out;
}

.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}

.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
