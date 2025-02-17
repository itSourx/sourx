<template>
    <div v-loading="loading" element-loading-text="Chargement des dossiers...">
        <el-table :data="paginatedFolders" style="width: 100%">
            <el-table-column prop="fields.name" label="Nom du dossier" min-width="200">
                <template #default="{ row }">
                    <div class="flex items-center gap-3 cursor-pointer" @click="handleFolderClick(row.id)">
                        <DocumentIcon :fileName="row.fields.name" class="folder-icon" />
                        <p class="text-sm">{{ row.fields.name }}</p>
                    </div>
                </template>
            </el-table-column>

            <el-table-column prop="fields.files" label="Fichiers" width="300">
                <template #default="{ row }">
                    <p>
                        <strong>{{ calculateTotalSize(row.fields['size (from files)']) }} - {{
                            (row.fields.files?.length ?? 0) }}</strong>
                        {{ row.fields.files?.length === 1 ? 'fichier' : 'fichiers' }}
                    </p>
                </template>
            </el-table-column>

            <el-table-column prop="createdBy" label="Créé par" width="200" sortable>
                <template #default="{ row }">
                    <small>
                        <strong>{{ row.fields['first_name (from created_by)'][0] }}
                            {{ row.fields['last_name (from created_by)'][0] }}</strong>
                    </small>
                </template>
            </el-table-column>

            <el-table-column label="Actions" width="100">
                <template #default="{ row }">
                    <el-button v-if="isCurrentUserFolder(row)" @click.stop="confirmDeleteFolder(row.id)">
                        <el-icon class="delete-icon">
                            <Delete />
                        </el-icon>
                    </el-button>
                </template>
            </el-table-column>
        </el-table>

        <div v-if="paginatedFolders.length === 0" class="text-center py-10">
            <p class="text-sm text-gray-600">Pas de dossier...</p>
        </div>

        <div class="pagination-container">
            <el-pagination v-if="totalFolders > pageSize" :current-page="currentPage" :page-size="pageSize"
                :total="totalFolders" layout="prev, pager, next" @current-change="handlePageChange" />
        </div>
    </div>
</template>

<script lang="ts" setup>
import { onMounted, ref, watch, nextTick, computed } from 'vue';
import { useRouter } from 'vue-router';
import DocumentIcon from '@/components/DocumentIcon.vue';
import { useFolderStore } from '@/stores/folderStore';
import { useAuthStore } from '@/stores/authStore';
import { ElMessageBox } from 'element-plus';

const loading = ref(false);
const folderList = ref([]);
const router = useRouter();
const folderStore = useFolderStore();
const userStore = useAuthStore();
const userFullName = `${userStore.user.first_name} ${userStore.user.last_name}`;
const currentPage = ref(1);
const pageSize = 10;
const totalFolders = ref(0);

const props = defineProps({
    reloadKey: Number,
});

const paginatedFolders = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    const end = start + pageSize;
    return folderList.value.slice(start, end);
});

function calculateTotalSize(sizes) {
    if (!sizes || !Array.isArray(sizes)) {
        return '0 Ko';
    }

    const totalBytes = sizes.reduce((total, size) => total + size, 0);
    let formattedSize;

    if (totalBytes >= 1024) {
        const totalMo = totalBytes / 1024;
        formattedSize = totalMo.toFixed(2) + ' Mo';
    } else {
        formattedSize = totalBytes.toFixed(2) + ' Ko';
    }

    return formattedSize;
}

const goToFolder = async (folderId) => {
    try {
        await nextTick();
        await router.push(`/folder/${folderId}`);
    } catch (error) {
        console.error("Router push error:", error);
    }
};

const handleFolderClick = async (folderId) => {
    if (loading.value) return;

    loading.value = true;
    try {
        await goToFolder(folderId);
    } catch (error) {
        console.error('Navigation error:', error);
    } finally {
        loading.value = false;
    }
};

const loadFolders = async (reload = false) => {
    loading.value = true;
    try {
        await folderStore.getAllFolders(reload);
        folderList.value = folderStore.folders;
        totalFolders.value = folderStore.totalFolders;
    } finally {
        loading.value = false;
    }
};

const handlePageChange = (page) => {
    currentPage.value = page;
};

const isCurrentUserFolder = (folder) => {
    const createdByFullName = `${folder.fields['first_name (from created_by)'][0]} ${folder.fields['last_name (from created_by)'][0]}`;
    return createdByFullName === userFullName;
};

const confirmDeleteFolder = (folderId) => {
    ElMessageBox.confirm('Êtes-vous sûr de vouloir supprimer ce dossier ?', 'Confirmation', {
        confirmButtonText: 'Oui',
        cancelButtonText: 'Annuler',
        type: 'warning'
    }).then(async () => {
        await folderStore.deleteFolder(folderId);
        await loadFolders(true);
    }).catch(() => {
        console.log('Suppression annulée');
    });
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
.delete-icon {
    color: #ff4d4f;
}

.folder-icon {
    width: 32px;
    height: 32px;
}

.bg-gray-100 {
    background-color: #F7F7F7;
}

.text-gray-600 {
    color: #4A5568;
}

.delete-button {
    position: absolute;
    top: 10px;
    right: 10px;
}

.folder-container {
    position: relative;
}

.pagination-container {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}
</style>