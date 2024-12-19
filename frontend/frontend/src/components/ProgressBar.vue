<template>
    <div class="demo-progress bg-gray-100 p-6 rounded-lg">
        <h3 class="text-sm font-bold">Espace utilisé</h3>
        <small class="mb-2">zrzr</small>
        <el-progress :text-inside="true" :stroke-width="20" :percentage="70" />
    </div>
</template>

<style scoped>
.demo-progress {}

.demo-progress .el-progress--line {
    margin-bottom: 15px;
}
</style>