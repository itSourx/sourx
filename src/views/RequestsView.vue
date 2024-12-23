<template>
    <div class="max-w-7xl mx-auto mt-4">
        <div class="flex justify-between items-center my-8">
            <h3 class="text-2xl font-bold">
                {{ authStore.user.role === 'Director' ? 'Demandes à valider' : 'Mes Demandes' }}
            </h3>
            <el-button type="primary" class="ml-auto" size="large" @click="toggleNewDemandArea"
                v-if="authStore.user.role != 'Director'">
                Nouvelle demande
                <el-icon class="el-icon--right">
                    <Upload />
                </el-icon>
            </el-button>
        </div>

        <!-- Espace pour créer une nouvelle demande -->
        <div v-if="toggleDemandArea" class="bg-gray-100 p-8 my-5 border border-gray-300 rounded-lg">
            <el-form ref="formRef" @submit.prevent="createNewRequest" label-position="top" label-width="100px">
                <el-form-item label="Motif de la demande" prop="selectedReason">
                    <el-select v-model="selectedReason" size="large" placeholder="Sélectionnez un motif">
                        <el-option v-for="reason in requestReasons" :key="reason.id" :label="reason.reason_title"
                            :value="reason.id" />
                    </el-select>
                </el-form-item>

                <el-form-item label="Description">
                    <el-input v-model="description" type="textarea" placeholder="Décrivez la demande ici" />
                </el-form-item>

                <el-form-item label="Fichiers justificatifs" prop="uploadedFiles">
                    <el-upload v-model:file-list="fileList" class="upload-demo w-full" drag :auto-upload="false"
                        limit="5" action="https://run.mocky.io/v3/9d059bf9-4660-45f2-925d-ce80ad6c4d15" multiple
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
                </el-form-item>

                <el-form-item v-loading="loading">
                    <el-button type="primary" native-type="submit" size="large">Valider la demande</el-button>
                </el-form-item>
            </el-form>
        </div>

        <!-- Tableau des demandes -->
        <RequestTable :key="tableKey" :requestReasons="requestReasons" />
    </div>
</template>

<script lang="ts" setup>
import { ref, onMounted, provide } from 'vue';
import { UploadFilled, Upload } from '@element-plus/icons-vue';
import RequestTable from '@/components/RequestTable.vue';
import { useRequestReasons } from '@/stores/requestReasonsStore'
import { useRequestStore } from '@/stores/requestStore'
import { useAuthStore } from '@/stores/authStore';
import { ElMessage } from 'element-plus';
import type { FormInstance } from 'element-plus';

const requestReasonStore = useRequestReasons()
const requestStore = useRequestStore()
const authStore = useAuthStore();

const loading = ref(false)
const requestReasons = ref([])
const toggleDemandArea = ref(false);
const selectedReason = ref(null);
const description = ref('');
const fileList = ref([]);
const formRef = ref<FormInstance>();
const tableKey = ref(0); // Initialise une clé réactive

const reloadTable = () => {
    tableKey.value += 1; // Incrémente la clé pour forcer le rechargement
};

/* const rules = {
    selectedReason: [{ required: true, message: 'Veuillez sélectionner un motif', trigger: 'change' }],
};
 */

const toggleNewDemandArea = () => {
    toggleDemandArea.value = !toggleDemandArea.value;
};

const createNewRequest = async () => {
    if (selectedReason.value == null) return;
    try {
        const formData = new FormData();
        formData.append('request_reason_id', selectedReason.value);
        formData.append('description', description.value);
        fileList.value.forEach(file => {
            formData.append('files[]', file.raw);
        });
        loading.value = true
        await requestStore.createRequest(formData)
        ElMessage.success('Demande enrégistrée');
        loading.value = false
        toggleDemandArea.value = !toggleDemandArea.value;

        reloadTable();

    } catch (error) {
        ElMessage.error('Erreur lors de la creation de la demande');
    }

};

onMounted(async () => {
    await requestReasonStore.fetchRequestReasons()
    requestReasons.value = requestReasonStore.requestReasons
})
</script>

<style scoped>
.upload-demo {
    margin-top: 20px;
}
</style>
