<template>
    <el-avatar>{{ initials }}</el-avatar>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps(['userName']);

const initials = computed(() => {
    if (!props.userName) return '';

    const nameParts = props.userName.split(' ');
    const firstInitial = nameParts[0] ? nameParts[0][0].toUpperCase() : '';
    const secondInitial = nameParts[1] ? nameParts[1][0].toUpperCase() : '';

    return firstInitial + secondInitial;
});
</script>

<style scoped>
.el-avatar {
    background-color: #f0f0f0;
    color: #333;
    font-weight: bold;
}
</style>