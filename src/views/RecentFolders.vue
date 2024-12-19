<template>
    <div class="recent-folders w-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 cursor-pointer"
        v-loading="loading" element-loading-text="Chargement des dossiers...">
        <el-card v-for="folder in folderList" :key="folder.folderId" shadow="hover" class="folder-card"
            @click="handleFolderClick(folder.id)">
            <div class="flex items-center mb-2">
                <DocumentIcon :fileName="folder.fields.name" />
                <h2 class="text-lg font-semibold ml-3">{{ folder.fields.name }}</h2>
            </div>

            <div class="text-gray-600">
                <p><strong>{{ calculateTotalSize(folder.fields['size (from files)']) }} - {{
                    (folder.fields.files?.length ?? 0) }}</strong>
                    élément(s)
                </p>
                <small>Créé par: <strong>{{ folder.fields['first_name (from created_by)'][0] }}
                        {{ folder.fields['last_name (from created_by)'][0] }}</strong> </small>
            </div>
        </el-card>
    </div>
</template>


<script lang="ts" setup>
import { onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { ElCard } from 'element-plus';
import DocumentIcon from '@/components/DocumentIcon.vue';
import { useFolderStore } from '@/stores/folderStore';
import { ElLoading } from 'element-plus';

const loading = ref(false)
const isClicking = ref(false);
const folderList = ref([]);
const router = useRouter();
const folderStore = useFolderStore();

const props = defineProps({
    reloadKey: Number,
});


function calculateTotalSize(sizes) {
    if (!sizes || !Array.isArray(sizes)) {
        return '0 Ko';
    }

    const totalBytes = sizes.reduce((total, size) => total + size, 0);
    let formattedSize;

    if (totalBytes >= 500) {
        const totalMo = totalBytes / 1024;
        formattedSize = totalMo.toFixed(2) + ' Mo';
    } else {
        formattedSize = totalBytes.toFixed(2) + ' Ko';
    }

    return formattedSize;
}

const goToFolder = async (folderId: number) => {
    await router.push(`/folder/${folderId}`);
};

const handleFolderClick = async (folderId: number) => {
    if (isClicking.value) return;
    isClicking.value = true;

    try {
        await goToFolder(folderId);
    } finally {
        isClicking.value = false;
    }
};

const loadFolders = async (reload = false) => {
    loading.value = true;
    try {
        await folderStore.getAllFolders(reload);
        folderList.value = folderStore.folders;
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    await loadFolders();
});

watch(
    () => props.reloadKey,
    () => {
        loadFolders(true);
    }
);
</script>

<style scoped>
.folder-card {
    transition: transform 0.2s;
    padding: 20px;
}
</style>
