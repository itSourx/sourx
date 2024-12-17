<template>
  <div>
    <NavBar v-if="!isAuthRoute" id="container" />
    <div class="main">
      <RouterView />
    </div>
  </div>

</template>

<script setup>
import { RouterView, useRoute } from 'vue-router'
import NavBar from '@/components/NavBar.vue'
import { computed } from 'vue'

const route = useRoute()

const isAuthRoute = computed(() => {
  return route.path.startsWith('/auth')
})
</script>
