<template>
    <div class="bg-gray-100 p-8 my-5 border border-gray-300 rounded-lg">
        <!-- Zone de drag & drop pour l'upload -->
        <el-upload v-model:file-list="fileList" class="upload-demo" drag :auto-upload="false" limit="5"
            action="https://run.mocky.io/v3/9d059bf9-4660-45f2-925d-ce80ad6c4d15" multiple
            accept=".jpg,.png,.pdf,.doc,.docx,.txt" :show-file-list="true" :on-change="handleUploadSuccess">
            <el-icon class="el-icon--upload">
                <UploadFilled />
            </el-icon>
            <div class="el-upload__text">
                Déposez le fichier ici ou <em>cliquez pour télécharger</em>
            </div>
            <template #tip>
                <div class="el-upload__tip">
                    Fichiers jpg/png et documents avec une taille inférieure à 500kb
                </div>
            </template>
        </el-upload>

        <div v-if="uploadComplete" class="mt-6">
            <el-form label-position="top" label-width="100px">

                <!-- Sélecteur de partage -->
                <el-form-item label="Partager avec">
                    <el-select v-model="shareOption" size="large" placeholder="Choisir une option"
                        @change="handleShareOptionChange">
                        <el-option label="Moi seul" value="self" />
                        <el-option label="À un collegue" value="individual" />
                        <el-option label="À une équipe" value="team" />
                    </el-select>
                </el-form-item>

                <el-form-item v-if="shareOption === 'self'" label="Sélectionnez le dossier">
                    <el-select v-model="selectedFolder" size="large" placeholder="Choisir un dossier">
                        <el-option v-for="folder in folderList" :key="folder.id" :label="folder.fields.name"
                            :value="folder.id" />
                    </el-select>
                </el-form-item>

                <!-- Sélection d'un membre si partage individuel -->
                <el-form-item v-if="shareOption === 'individual'" label="Sélectionnez un collègue">
                    <el-select v-model="selectedMember" multiple size="large" placeholder="Choisir un collègue">
                        <el-option v-for="member in userList" :key="member.id"
                            :label="`${member.first_name} ${member.last_name}`" :value="member.id" />
                    </el-select>
                </el-form-item>

                <!-- Sélection d'une équipe si partage avec une équipe -->
                <el-form-item v-if="shareOption === 'team'" label="Sélectionnez une équipe">
                    <el-select v-model="selectedTeam" size="large" placeholder="Choisir une équipe">
                        <el-option v-for="team in teamList" :key="team.id" :label="team.fields.name" :value="team.id" />
                    </el-select>
                </el-form-item>

                <el-form-item>
                    <el-button type="primary" @click="submitForm">Ajouter</el-button>
                </el-form-item>

            </el-form>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useFolderStore } from '@/stores/folderStore'
import { useTeamStore } from '@/stores/teamStore'
import { useDocumentStore } from '@/stores/documentStore'
import { UploadFilled } from '@element-plus/icons-vue';
import { ElMessage } from 'element-plus';

const userStore = useAuthStore()
const folderStore = useFolderStore()
const teamStore = useTeamStore()
const documentStore = useDocumentStore()

const userList = ref([])
const folderList = ref([])
const teamList = ref([])
const fileList = ref([]);

const selectedFolder = ref('');
const shareOption = ref('self');
const selectedMember = ref([]);
const selectedTeam = ref([]);
const uploadComplete = ref(false);

const emit = defineEmits(['documentUploaded']);

const handleUploadSuccess = () => {
    uploadComplete.value = true;
};

// Gérer le changement de l'option de partage
const handleShareOptionChange = (value: string) => {
    selectedMember.value = [];
    selectedTeam.value = [];
};

const submitForm = async () => {
    const formData = new FormData();

    // Ajoutez les données que vous voulez envoyer
    formData.append('creatorId', userStore.user.user_id);
    formData.append('folderId', selectedFolder.value);
    formData.append('shareOption', shareOption.value);
    formData.append('selectedMember', JSON.stringify(selectedMember.value));
    formData.append('selectedTeam', JSON.stringify(selectedTeam.value));

    fileList.value.forEach(file => {
        formData.append('files[]', file.raw);
    });

    try {
        await documentStore.createDocument(formData);
        ElMessage.success('Nouveau document ajouté');
        /* await documentStore.getAllDocuments(true); */
        selectedFolder.value = '';
        shareOption.value = 'self';
        selectedMember.value = [];
        selectedTeam.value = [];
        fileList.value = [];

        emit('documentUploaded');
    } catch (error) {
        console.error("Erreur lors de la soumission du formulaire:", error);
        ElMessage.error("Échec d'ajout du fichier");
    }
};

onMounted(async () => {
    userStore.fetchUser()
    await userStore.fetchUsers()
    await folderStore.getAllFolders()
    await teamStore.fetchTeams()
    folderList.value = folderStore.folders
    teamList.value = teamStore.teams

    if (userStore.user.role == 'Employee') {
        userList.value = userStore.users.filter(user => user.role === 'Employee')
    }
    else {
        userList.value = userStore.users.filter(user => (user.role === 'Employee' || user.role === 'Manager'))
    }

    // Filtrer folderList en fonction du userStore.user
    const currentUser = userStore.user;
    folderList.value = folderList.value.filter(folder => {
        const folderLastName = folder.fields["last_name (from created_by)"]?.[0];
        const folderFirstName = folder.fields["first_name (from created_by)"]?.[0];
        return folderLastName === currentUser.last_name && folderFirstName === currentUser.first_name;
    });

    console.log(folderList.value)
})

</script>

<style scoped>
.upload-demo {
    margin-top: 20px;
}

.el-upload-list__item-name {
    padding: 4px !important;
}
</style>
