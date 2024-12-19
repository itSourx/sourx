<template>
    <div class="common-layout w-full h-screen">
        <el-container class="h-full">
            <el-aside width="50%" class="flex items-center justify-center">
                <img src="@/assets/Computer login.svg" alt="Aside Image" class="w-1/2 h-auto" />
            </el-aside>
            <el-divider direction="vertical" style="height: 100%;" />

            <el-main class="flex flex-col items-center justify-center h-full">
                <router-view />
            </el-main>
        </el-container>
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

onMounted(() => {
    router.push('/auth/login')
});
</script>