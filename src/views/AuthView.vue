<template>
    <div class="common-layout w-full h-screen">
      <el-container class="h-full">
        <!-- Image section -->
        <el-aside 
          width="50%" 
          class="hidden md:flex items-center justify-center">
          <img src="@/assets/Computer login.svg" alt="Aside Image" class="w-1/2 h-auto" />
        </el-aside>
        
        <!-- Divider (only visible on medium screens and above) -->
        <el-divider 
          direction="vertical" 
          style="height: 100%;" 
          class="hidden md:block" 
        />
  
        <!-- Main section -->
        <el-main class="flex flex-col items-center justify-center h-full w-full">
          <router-view />
        </el-main>
      </el-container>
    </div>
  </template>
  
  <script setup>
  import { onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  
  const router = useRouter();
  
  onMounted(() => {
    router.push('/auth/login');
  });
  </script>
  