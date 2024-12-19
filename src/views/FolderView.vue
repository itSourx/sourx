<template>
    <div v-if="currentFolder" class="folder-view max-w-7xl mx-auto mt-4">
        <!-- Breadcrumb -->
        <el-breadcrumb separator="/">
            <el-breadcrumb-item :to="{ path: '/home' }">
                <a>Mes dossiers</a>
            </el-breadcrumb-item>
            <el-breadcrumb-item>{{ currentFolder.fields.name }}</el-breadcrumb-item>
        </el-breadcrumb>

        <div class="flex justify-between items-center my-8">
            <h3 class="text-xl font-bold"> {{ currentFolder.fields.name }} </h3>
            <el-button type="primary" class="ml-auto" size="large" @click="toggleUploadArea">
                Nouveau document
                <el-icon class="el-icon--right">
                    <Upload />
                </el-icon>
            </el-button>
        </div>

        <DocumentUploader v-if="showUploadDocument" />

        <!-- Checkbox pour filtrer -->
        <div class="mb-4">
            <el-checkbox v-model="filterByCurrentUser">
                Afficher uniquement mes documents
            </el-checkbox>
        </div>

        <!-- Table des fichiers -->
        <el-table :data="filteredFiles" style="width: 100%" class="mt-4" v-loading="loading"
            empty-text="Pas de documents">
            <el-table-column prop="name" label="Nom du Fichier" sortable>
                <template #default="scope">
                    <div class="flex items-center">
                        <DocumentIcon :fileName="scope.row.name" />
                        <a :href="scope.row.url" target="_blank" rel="noopener noreferrer"
                            class="text-blue-600 hover:underline">
                            {{ scope.row.name }}
                        </a>
                    </div>
                </template>
            </el-table-column>

            <el-table-column prop="size" label="Taille" sortable>
                <template #default="scope">
                    <span> {{ scope.row.size }} Ko </span>
                </template>
            </el-table-column>

            <el-table-column prop="created_at" label="Date de Création" sortable>
                <template #default="scope">
                    <span> {{ formatDate(scope.row.created_at) }} </span>
                </template>
            </el-table-column>

            <el-table-column label="Envoyé par" sortable>
                <template #default="scope">
                    <UserAvatar :userName="scope.row.uploaded_by" class="mx-1" />
                    {{ scope.row.uploaded_by }}
                </template>
            </el-table-column>

            <el-table-column label="Actions">
                <template #default="scope">
                    <el-button size="large" @click="downloadFile(scope.row.url)">
                        <el-icon>
                            <Download />
                        </el-icon>
                    </el-button>
                    <el-button size="large" type="danger" @click="removeFile(scope.row)">
                        <el-icon>
                            <Delete />
                        </el-icon>
                    </el-button>
                </template>
            </el-table-column>
        </el-table>
    </div>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import UserAvatar from '@/components/UserAvatar.vue';
import DocumentIcon from '@/components/DocumentIcon.vue';
import DocumentUploader from '@/components/DocumentUploader.vue';
import { useFolderStore } from '@/stores/folderStore';
import { useAuthStore } from '@/stores/authStore';
import { useDocumentStore } from '@/stores/documentStore';
import { Download, Delete } from '@element-plus/icons-vue';
import { ElMessage } from 'element-plus';

// Variables réactives
const route = useRoute();
const folderId = ref<string | null>(null);
const files = ref([]);
const filterByCurrentUser = ref(false);
const currentFolder = ref(null);
const showUploadDocument = ref(false);
const loading = ref(false);

// Stores
const folderStore = useFolderStore();
const userStore = useAuthStore();
const documentStore = useDocumentStore();

// Utilisateur courant
const userFullName = `${userStore.user.first_name} ${userStore.user.last_name}`;

// Documents filtrés
const filteredFiles = computed(() => {
    if (filterByCurrentUser.value) {
        return files.value.filter(file => file.uploaded_by === userFullName);
    }
    return files.value;
});

// Actions
const toggleUploadArea = () => {
    showUploadDocument.value = !showUploadDocument.value;
};

const removeFile = async (file) => {
    loading.value = true;
    try {
        await documentStore.removeFile(file.id);
        files.value = files.value.filter(f => f.name !== file.name || f.created_at !== file.created_at);
        ElMessage.success('Document supprimé');
    } catch (error) {
        console.error("Erreur lors de la suppression:", error);
        ElMessage.error("Échec de suppression");
    }
    loading.value = false;
};

onMounted(() => {
    folderId.value = route.params.id as string;

    const folder = folderStore.folders.find(folder => folder.id === folderId.value);
    currentFolder.value = folder;

    if (folder) {
        // Vérification si 'folder.fields.files' existe et contient des données
        if (Array.isArray(folder.fields.files) && folder.fields.files.length > 0) {
            files.value = folder.fields.files.map((fileId, index) => ({
                id: fileId,
                name: folder.fields['name (from files)'][index],
                size: folder.fields['size (from files)'][index],
                created_at: folder.fields['created_at (from files)'][index],
                uploaded_by: folder.fields['first_name (from uploaded_by) (from files)'][index] + ' ' +
                    folder.fields['last_name (from uploaded_by) (from files)'][index],
                url: folder.fields['url (from files)'][index]
            }));
        } else {
            // Si aucun fichier n'est disponible, initialiser avec une liste vide
            files.value = [];
        }
    }
});

function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).replace(',', ' à');
}

const downloadFile = (url: string) => {
    window.open(url, '_blank');
};
</script>

<style scoped>
.folder-view {
    padding: 20px;
    border-radius: 8px;
}
</style>
