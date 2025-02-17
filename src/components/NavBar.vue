<template>
  <el-menu :default-active="activeIndex" class="el-menu-demo" mode="horizontal" :ellipsis="false" @select="handleSelect"
    v-if="authStore.user">
    <el-menu-item class="logo-item">
      <router-link to="/home">
        <img style="width: 100px" src="https://sourx.com/wp-content/uploads/2023/08/sourx-emea-format-es.-copy.png"
          alt="Sourx logo" />
      </router-link>
    </el-menu-item>

    <el-menu-item index="1" v-if="authStore.user.role === 'Director'">
      <router-link to="/dashboard">Dashboard admin</router-link>
    </el-menu-item>

    <el-menu-item index="2">
      <router-link to="/documents">Documents</router-link>
    </el-menu-item>

    <el-menu-item index="3">
      <router-link to="/requests">Demandes</router-link>
    </el-menu-item>

    <el-menu-item index="4" v-if="authStore.user.role == 'Director' || authStore.user.role == 'Manager'">
      <router-link to="/models">Générateur</router-link>
    </el-menu-item>

    <el-sub-menu index="5" v-if="authStore.user.role == 'Director'">
      <template #title>Paramètres</template>
      <el-menu-item index="5-1">
        <router-link to="/settings/configuration">Mon entreprise</router-link>
      </el-menu-item>
      <el-menu-item index="5-2">
        <router-link to="/settings/user-management">Gestion des Utilisateurs</router-link>
      </el-menu-item>
      <el-menu-item index="5-3">
        <router-link to="/settings/request-reasons">Motifs des demandes</router-link>
      </el-menu-item>
      <el-menu-item index="5-4">
        <router-link to="/settings/team-management">Gestion des Équipes</router-link>
      </el-menu-item>
      <el-menu-item index="5-5">
        <router-link to="/settings/support">Support/Aide</router-link>
      </el-menu-item>
    </el-sub-menu>

    <el-sub-menu index="7">
      <template #title>
        <el-avatar :icon="UserFilled" class="mr-4" /> {{ authStore.user ? authStore.user.first_name : "" }}
      </template>
      <router-link to="/myprofil">
        <el-menu-item index="7-1">
          <el-icon class="mr-2">
            <UserFilled />
          </el-icon>
          Mon Profil
        </el-menu-item>
      </router-link>
      <el-menu-item index="7-2" @click="logout">
        <el-icon class="mr-2">
          <SwitchButton />
        </el-icon>
        Déconnexion
      </el-menu-item>
    </el-sub-menu>

    <!-- Hamburger Menu for Mobile -->
    <div class="mobile-menu-toggle" @click="toggleMobileMenu">
      <el-icon>
        <Menu />
      </el-icon>
    </div>
  </el-menu>

  <!-- Mobile Menu -->
  <div class="mobile-menu" v-if="mobileMenuOpen">
    <el-menu :default-active="activeIndex" class="el-menu-vertical-demo" @select="handleSelect">
      <el-menu-item index="1" v-if="authStore.user.role === 'Director'">
        <router-link to="/dashboard">Dashboard admin</router-link>
      </el-menu-item>
      <el-menu-item index="2">
        <router-link to="/documents">Documents</router-link>
      </el-menu-item>
      <el-menu-item index="3">
        <router-link to="/requests">Demandes</router-link>
      </el-menu-item>
      <el-menu-item index="4" v-if="authStore.user.role == 'Director' || authStore.user.role == 'Manager'">
        <router-link to="/models">Générateur</router-link>
      </el-menu-item>
      <el-sub-menu index="5" v-if="authStore.user.role == 'Director'">
        <template #title>Paramètres</template>
        <el-menu-item index="5-1">
          <router-link to="/settings/configuration">Mon entreprise</router-link>
        </el-menu-item>
        <el-menu-item index="5-2">
          <router-link to="/settings/user-management">Gestion des Utilisateurs</router-link>
        </el-menu-item>
        <el-menu-item index="5-3">
          <router-link to="/settings/request-reasons">Motifs des demandes</router-link>
        </el-menu-item>
        <el-menu-item index="5-4">
          <router-link to="/settings/team-management">Gestion des Équipes</router-link>
        </el-menu-item>
        <el-menu-item index="5-5">
          <router-link to="/settings/support">Support/Aide</router-link>
        </el-menu-item>
      </el-sub-menu>
      <el-sub-menu index="7">
        <template #title>
          <el-avatar :icon="UserFilled" class="mr-4" /> {{ authStore.user ? authStore.user.first_name : "" }}
        </template>
        <router-link to="/myprofil">
          <el-menu-item index="7-1">
            <el-icon class="mr-2">
              <UserFilled />
            </el-icon>
            Mon Profil
          </el-menu-item>
        </router-link>
        <el-menu-item index="7-2" @click="logout">
          <el-icon class="mr-2">
            <SwitchButton />
          </el-icon>
          Déconnexion
        </el-menu-item>
      </el-sub-menu>
    </el-menu>
  </div>
</template>


<script lang="ts" setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { UserFilled, Menu, SwitchButton } from '@element-plus/icons-vue'
import { useAuthStore } from '@/stores/authStore'

const activeIndex = ref('1')
const route = useRoute()
const handleSelect = (key: string, keyPath: string[]) => {
  console.log(key, keyPath)
}

const authStore = useAuthStore()
const logout = () => {
  authStore.logout()
}

const mobileMenuOpen = ref(false)

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

watch(route, (newRoute) => {
  // Mettez à jour activeIndex en fonction du chemin de la route
  if (newRoute.path === '/dashboard') {
    activeIndex.value = '1'
  } else if (newRoute.path === '/documents') {
    activeIndex.value = '2'
  } else if (newRoute.path === '/requests') {
    activeIndex.value = '3'
  } else if (newRoute.path === '/models') {
    activeIndex.value = '4'
  } else if (newRoute.path === '/settings/configuration') {
    activeIndex.value = '5-1'
  } else if (newRoute.path === '/settings/user-management') {
    activeIndex.value = '5-2'
  } else if (newRoute.path === '/settings/request-reasons') {
    activeIndex.value = '5-3'
  } else if (newRoute.path === '/settings/team-management') {
    activeIndex.value = '5-4'
  } else if (newRoute.path === '/settings/support') {
    activeIndex.value = '5-5'
  } else if (newRoute.path === '/myprofil') {
    activeIndex.value = '7-1'
  } else {
    activeIndex.value = '1'  // Valeur par défaut
  }
})
</script>

<style scoped>
.el-menu--horizontal>.el-menu-item:nth-child(1) {
  margin-right: auto;
}

.mobile-menu-toggle {
  display: none;
  cursor: pointer;
  font-size: 24px;
  padding: 10px;
}

.mobile-menu {
  display: none;
  position: absolute;
  top: 60px;
  left: 0;
  width: 100%;
  background-color: #fff;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
  z-index: 1000;
}

@media (max-width: 768px) {
  .el-menu--horizontal .el-menu-item,
  .el-menu--horizontal .el-sub-menu {
    display: none;
  }

  .mobile-menu-toggle {
    display: block;
  }

  .mobile-menu {
    display: block;
  }

  .logo-item {
    margin-left: auto;
  }
}
</style>
