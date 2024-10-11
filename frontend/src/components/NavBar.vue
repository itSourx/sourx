<template>
  <nav v-if="user" class="bg-blue py-4 relative">
    <div class="container mx-auto flex items-center justify-between">
      <!-- Combined Left Section: Accueil and Navigation Links -->
      <div class="flex items-center space-x-8">
        <h1 class="text-yellow font-bold text-lg">SOURX DOCS</h1>
        <router-link to="/home" v-if="!user.isAdmin" class="nav-link">Accueil</router-link>

        <!-- Menu Links for larger screens -->
        <div class="hidden lg:flex lg:space-x-8 items-center">
          <router-link to="/home/documents" v-if="!user.isAdmin" class="nav-link">
            Mes documents
          </router-link>

          <router-link to="/home/requests" v-if="!user.isAdmin && user.role == 'Salarie'" class="nav-link">
            Mes demandes
          </router-link>
          <router-link to="/home/myteam" v-if="user.isAdmin === 0 && user.role !== 'Salarie'" class="nav-link">
            Mon équipe
          </router-link>

          <!-- Admin Links -->
          <router-link to="/management" v-if="user.isAdmin" class="nav-link">
            Dashboard Admin
          </router-link>
          <router-link to="/management/documents" v-if="user.isAdmin" class="nav-link">
            Documents
          </router-link>

          <router-link to="/home/requests" v-if="user.role !== 'Salarie'" class="nav-link">
            Demandes
          </router-link>

          <!-- Dropdown for Settings with Utilisateurs and Equipes -->
          <div class="relative group">
            <router-link to="/management/administration" v-if="user.isAdmin" class="nav-link">
              Paramètres
            </router-link>
          </div>
        </div>
      </div>

      <!-- Right Section: User Profile -->
      <div class="flex items-center space-x-4 ml-auto">
        <div class="relative" @click="toggleDropdown">
          <img v-if="user.photo" :src="user.photo" alt="User Photo"
            class="w-10 h-10 rounded-full object-cover cursor-pointer" />
          <div v-else
            class="w-10 h-10 rounded-full bg-gray-medium text-white flex items-center justify-center cursor-pointer font-semibold">
            {{ getUserInitials(user.nom) }}
          </div>

          <!-- Dropdown Menu -->
          <transition name="slide-fade">
            <div v-if="dropdownOpen" class="dropdown-menu">
              <router-link to="/home/settings" class="dropdown-item">
                <UserRound class="w-4 h-4 mr-2" /> Profil
              </router-link>
              <p @click="logout" class="dropdown-item cursor-pointer">
                <LogOut class="w-4 h-4 mr-2" /> Déconnexion
              </p>
            </div>
          </transition>
        </div>

        <!-- Display User's First Name or Role Tag -->
        <span v-if="user.role === 'Salarie'" class="text-white">{{ user.prenom }}</span>
        <UserRoleTag v-else :role="user.role"></UserRoleTag>
      </div>
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

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
}

const logout = () => {
  loginStore.logout(router)
}
</script>

<style scoped>
.nav-link {
  @apply text-white text-sm font-medium hover:font-semibold transition duration-200 ease-in-out;
}

.dropdown-menu {
  @apply absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-10 transition ease-out duration-200 transform translate-x-1/2;
}

.dropdown-item {
  @apply flex items-center px-4 py-2 text-gray-medium hover:bg-gray-light rounded cursor-pointer transition duration-150 ease-in-out;
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

/* Dropdown for settings with slide effect */
.slide-dropdown {
  transition: max-height 0.3s ease-in-out, opacity 0.3s ease-in-out;
  max-height: 0;
  overflow: hidden;
  opacity: 0;
}

.group-hover .slide-dropdown {
  max-height: 300px;
  /* Adjust based on content height */
  opacity: 1;
}
</style>
