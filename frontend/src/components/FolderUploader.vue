<template>
    <div class="bg-gray-100 p-8 my-5 border border-gray-300 rounded-lg">
        <el-form @submit.prevent="handleFolderCreation" label-position="top">
            <el-form-item label="Nom du dossier" required>
                <el-input v-model="folderName" placeholder="Entrez le nom du dossier" size="large" />
            </el-form-item>

            <el-form-item label="Télécharger des fichiers">
                <el-upload v-model:file-list="fileList" class="upload-demo w-full" drag :auto-upload="false"
                    action="https://run.mocky.io/v3/9d059bf9-4660-45f2-925d-ce80ad6c4d15" multiple
                    :on-change="handleFileChange" :show-file-list="true" accept=".jpg,.png,.pdf,.doc,.docx,.txt">
                    <el-icon class="el-icon--upload">
                        <UploadFilled />
                    </el-icon>
                    <div class="el-upload__text">
                        Déposez le fichier ici ou <em>cliquez pour télécharger</em>
                    </div>
                </el-upload>
            </el-form-item>

            <el-form-item>
                <el-button type="primary" size="large" @click="handleFolderCreation" class="w-full">Créer le
                    dossier</el-button>
            </el-form-item>
        </el-form>
    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue';
import { UploadFilled } from '@element-plus/icons-vue';
import { useFolderStore } from '@/stores/folderStore';
import { useAuthStore } from '@/stores/authStore';
import { ElMessage } from 'element-plus';

const folderName = ref('');
const fileList = ref([]);
const folderStore = useFolderStore();
const authStore = useAuthStore();

const userId = ref(authStore.user.user_id);

const handleFileChange = (file) => {
    console.log("Change" + file)
};

const emit = defineEmits(['folderCreated']);

const handleFolderCreation = async () => {
    const formData = new FormData();

    formData.append('folderName', folderName.value);
    formData.append('creatorId', userId.value);

    fileList.value.forEach((file, index) => {
        formData.append(`files[${index}]`, file.raw);
    });

    console.log(Array.from(formData.entries()));
    try {
        await folderStore.createFolder(formData);
        ElMessage.success('Dossier créé avec succès !');
        emit('folderCreated');
        folderName.value = '';
        fileList.value = [];
    } catch (error) {
        console.error('Erreur lors de la création du dossier', error);
        ElMessage.error('Erreur lors de la création du dossier');
    }
};
</script>

<style scoped>
.upload-demo {
    margin-top: 20px;
}
</style>