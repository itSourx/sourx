<template>
  <div class="w-full sm:w-full md:w-12/12 flex flex-col min-h-screen">

    <header class="mb-8 bg-oxford-blue">
      <NavBar />
    </header>

    <main class="flex-1">
      <router-view></router-view>
    </main>

    <footer class="bg-oxford-blue text-white py-4 mt-8 text-center">
      © 2024 Sourx Docs. Tous droits réservés.
    </footer>

  </div>
</template>

<script setup>
import NavBar from '@/components/NavBar.vue'
</script>
