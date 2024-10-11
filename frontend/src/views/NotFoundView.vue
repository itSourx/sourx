<template>
  <div class="flex flex-col items-center justify-center h-screen">
    <h1 class="text-4xl font-bold mb-4">Page not found</h1>
    <p class="text-lg">Sorry, the page you are looking for does not exist.</p>
    <router-link to="/" class="text-zaffre hover:underline mt-4">Go to Home</router-link>
  </div>
</template>

<script>
export default {
  name: 'NotFoundView'
}
</script>

<style scoped>
/* Styles spécifiques à la page 404 */
</style>
