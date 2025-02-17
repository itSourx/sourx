<template>
    <div class="requests-table w-full overflow-x-auto my-5">
        <!-- Barre de recherche -->
        <div class="mb-4">
            <el-input v-model="search" size="large" placeholder="Rechercher" clearable
                :prefix-icon="Search" class="w-full" />
        </div>

        <el-tabs v-model="activeTab" @tab-click="onTabClick">
            <!-- Onglet "Mes demandes" -->
            <el-tab-pane label="Mes demandes" name="myRequests" v-if="authStore.user.role != 'Director'">
                <el-table :data="myRequests" style="width: 100%">
                    <el-table-column label="Motif">
                        <template #default="scope">
                            <span>{{ getReasonName(scope.row.fields.reason[0]) }}</span>
                        </template>
                    </el-table-column>

                    <el-table-column label="Justificatifs">
                        <template #default="scope">
                            <div v-if="scope.row.fields.justification_files?.length">
                                <el-popover placement="top" width="250" trigger="hover">
                                    <div v-for="(fileName, index) in scope.row.fields['name (from justification_files)']"
                                        :key="index" class="mb-2 last:mb-0">
                                        <a class="flex items-center space-x-2 hover:text-blue-500"
                                            :href="scope.row.fields['url (from justification_files)'][index]"
                                            target="_blank">
                                            <DocumentIcon :fileName="fileName" />
                                            <span>{{ fileName }}</span>
                                        </a>
                                    </div>
                                    <template #reference>
                                        <el-button type="text">
                                            {{ scope.row.fields.justification_files.length }} {{
                                                scope.row.fields.justification_files.length > 1 ? 'fichiers' : 'fichier' }}
                                        </el-button>
                                    </template>
                                </el-popover>
                            </div>
                            <div v-else>Aucun justificatif</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="Réponse">
                        <template #default="scope">
                            <div v-if="scope.row.fields.response_files?.length">
                                <el-popover placement="top" width="250" trigger="hover">
                                    <div v-for="(fileName, index) in scope.row.fields['name (from response_files)']"
                                        :key="index" class="mb-2 last:mb-0">
                                        <a class="flex items-center space-x-2 hover:text-blue-500"
                                            :href="scope.row.fields['url (from response_files)'][index]"
                                            target="_blank">
                                            <DocumentIcon :fileName="fileName" />
                                            <span>{{ fileName }}</span>
                                        </a>
                                    </div>
                                    <!-- Ajoutez un bouton pour déclencher le popover -->
                                    <template #reference>
                                        <el-button type="text">
                                            {{ scope.row.fields.response_files.length }} {{
                                                scope.row.fields.response_files.length > 1 ? 'fichiers' : 'fichier' }}
                                        </el-button>
                                    </template>
                                </el-popover>
                            </div>
                            <div v-else>Aucun fichier</div>
                        </template>
                    </el-table-column>


                    <el-table-column prop="fields.created_at" label="Créé le" width="150" sortable>
                        <template #default="scope">
                            <p>{{ new Date(scope.row.fields.created_at).toLocaleDateString() }}</p>
                        </template>
                    </el-table-column>

                    <el-table-column prop="fields.progression" label="Statut" width="120" sortable>
                        <template #default="scope">
                            <el-tag :type="getProgressionTag(scope.row.fields.progression)">
                                {{ formatProgression(scope.row.fields.progression) }}
                            </el-tag>
                        </template>
                    </el-table-column>

                    <el-table-column label="Actions" width="200">
                        <template #default="scope">
                            <el-button @click="openEditModal(scope.row)" size="mini"
                                v-if="scope.row.fields.progression != 'completed' && scope.row.fields.progression != 'rejected'">
                                <el-icon>
                                    <Edit />
                                </el-icon>
                            </el-button>

                            <el-button type="danger" @click="deleteRequest(scope.row.id)" size="mini">
                                <el-icon>
                                    <Delete />
                                </el-icon>
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </el-tab-pane>

            <!-- Onglet "À valider" -->
            <el-tab-pane label="À valider" name="toValidate" v-if="authStore.user.role != 'Employee'"
                style="width: 100%">
                <el-table :data="toValidateRequests">

                    <el-table-column label="Motif" sortable>
                        <template #default="scope">
                            <span>{{ getReasonName(scope.row.fields.reason[0]) }}</span>
                        </template>
                    </el-table-column>

                    <el-table-column label="Justificatifs">
                        <template #default="scope">
                            <div v-if="scope.row.fields.justification_files?.length">
                                <el-popover placement="top" width="250" trigger="hover">
                                    <div v-for="(fileName, index) in scope.row.fields['name (from justification_files)']"
                                        :key="index" class="mb-2 last:mb-0">
                                        <a class="flex items-center space-x-2 hover:text-blue-500"
                                            :href="scope.row.fields['url (from justification_files)'][index]"
                                            target="_blank">
                                            <DocumentIcon :fileName="fileName" />
                                            <span>{{ fileName }}</span>
                                        </a>
                                    </div>
                                    <template #reference>
                                        <el-button type="text">
                                            {{ scope.row.fields.justification_files.length }} {{
                                                scope.row.fields.justification_files.length > 1 ? 'fichiers' : 'fichier' }}
                                        </el-button>
                                    </template>
                                </el-popover>
                            </div>
                            <div v-else>Aucun justificatif</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="Collaborateur">
                        <template #default="scope">
                            <!-- <div
                                v-if="scope.row.fields.responseDocuments && scope.row.fields.responseDocuments?.length">
                                <el-button type="text" @click="downloadDocuments(scope.row.fields.responseDocuments)">
                                    {{ scope.row.fields.responseDocuments.length }} {{
                                        scope.row.fields.responseDocuments.length > 1 ? 'fichiers' : 'fichier' }}
                                </el-button>
                            </div> -->
                            <UserAvatar
                                :userName="(scope.row.fields['first_name (from requester)']?.[0] || '') + ' ' + (scope.row.fields['last_name (from requester)']?.[0] || '')"
                                class="mx-1" />
                            {{ scope.row.fields["first_name (from requester)"]?.[0] || '' }}
                            {{ scope.row.fields["last_name (from requester)"]?.[0] || '' }}
                        </template>
                    </el-table-column>

                    <el-table-column prop="fields.created_at" label="Créé le" width="150" sortable>
                        <template #default="scope">
                            <p>{{ new Date(scope.row.fields.created_at).toLocaleDateString() }}</p>
                        </template>
                    </el-table-column>

                    <el-table-column prop="fields.progression" label="Statut" width="120" sortable>
                        <template #default="scope">
                            <el-tag :type="getProgressionTag(scope.row.fields.progression)">
                                {{ formatProgression(scope.row.fields.progression) }}
                            </el-tag>
                        </template>
                    </el-table-column>

                    <el-table-column label="Actions" width="300">
                        <template #default="scope">
                            <template v-if="scope.row.fields.progression === 'pending'">
                                <el-button @click="handleRequest(scope.row)" size="mini">Prendre en
                                    charge</el-button>
                            </template>
                            <template v-else-if="scope.row.fields.progression === 'in_progress'">
                                <el-button type="success" @click="openAcceptDialog(scope.row)"
                                    size="mini">Accepter</el-button>
                                <el-button type="danger" @click="rejectRequest(scope.row)"
                                    size="mini">Refuser</el-button>
                            </template>
                        </template>
                    </el-table-column>
                </el-table>
            </el-tab-pane>
        </el-tabs>

        <!-- Modale de modification -->
        <el-dialog v-model="editDialogVisible" title="Modifier la demande" width="500" left>
            <el-form :model="selectedRequest" ref="requestForm" label-width="120px" native-type="submit">
                <el-form-item label="Motif" label-position="top">
                    <el-select v-model="selectedRequest.fields.reason[0]" placeholder="Sélectionnez un motif"
                        size="large">
                        <el-option v-for="reason in props.requestReasons" :key="reason.id" :label="reason.reason_title"
                            :value="reason.id" />
                    </el-select>
                </el-form-item>
                <el-form-item label="Description" label-position="top">
                    <el-input v-model="selectedRequest.fields.description" placeholder="Description de la demande"
                        type="textarea" size="large" :rows="5" />
                </el-form-item>
            </el-form>
            <template #footer>
                <div class="dialog-footer">
                    <el-button @click="editDialogVisible = false">Annuler</el-button>
                    <el-button type="primary" @click="updateRequest(selectedRequest.id)">Enregistrer</el-button>
                </div>
            </template>
        </el-dialog>

        <!-- Modale d'acceptation de demande -->
        <el-dialog v-model="acceptDialogVisible" title="Accepter le document" width="30%"
            :before-close="handleDialogClose">
            <el-form label-position="top" label-width="100px">

                <el-form-item label="Description">
                    <el-input v-model="descriptionResponse" type="textarea"
                        placeholder="Ajouter une description (optionnel)" maxlength="500" show-word-limit></el-input>
                </el-form-item>

                <el-form-item label="Télécharger des fichiers">
                    <el-upload v-model:file-list="responseFileList" class="upload-demo" drag :auto-upload="false"
                        :limit="5" accept=".jpg,.png,.pdf,.doc,.docx,.txt" :show-file-list="true"
                        :on-change="handleUploadChange">
                        <el-icon class="el-icon--upload">
                            <UploadFilled />
                        </el-icon>
                        <div class="el-upload__text">
                            Déposez le fichier ici ou <em>cliquez pour télécharger</em>
                        </div>
                    </el-upload>
                </el-form-item>

                <el-form-item>
                    <el-button type="primary" @click="acceptRequest">Soumettre</el-button>
                    <el-button @click="handleDialogClose">Annuler</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted } from 'vue';
import { Search, Edit } from '@element-plus/icons-vue';
import { useRequestStore } from '@/stores/requestStore'
import { ElMessageBox, ElMessage } from 'element-plus';
import DocumentIcon from '@/components/DocumentIcon.vue';
import UserAvatar from '@/components/UserAvatar.vue';
import { useAuthStore } from '@/stores/authStore';
import { ElLoading } from 'element-plus';

const requestStore = useRequestStore()
const authStore = useAuthStore();

const requestList = ref([]);
const responseFileList = ref([]);
const search = ref('');
const descriptionResponse = ref("");
const selectedRequest = ref(null);
const activeTab = ref('myRequests');
const editDialogVisible = ref(false);
const acceptDialogVisible = ref(false);

const props = defineProps({
    requestReasons: Array,
});

const myRequests = computed(() => {
    console.log("---")
    console.log(requestList.value)
    return requestList.value
        .filter(request => !request.toBeValidated)
        .filter(request => (request.fields.description || '').toLowerCase().includes(search.value.toLowerCase()) ||
            getReasonName(request.fields.reason[0]).toLowerCase().includes(search.value.toLowerCase()));
});

const toValidateRequests = computed(() => {
    console.log("---")
    console.log(requestList.value
        .filter(request => request.toBeValidated && !['completed', 'rejected'].includes(request.fields.progression))
        .filter(request => (request.fields.description || '').toLowerCase().includes(search.value.toLowerCase()) ||
            getReasonName(request.fields.reason[0]).toLowerCase().includes(search.value.toLowerCase())))

    return requestList.value
        .filter(request => request.toBeValidated && !['completed', 'rejected'].includes(request.fields.progression))
        .filter(request => (request.fields.description || '').toLowerCase().includes(search.value.toLowerCase()) ||
            getReasonName(request.fields.reason[0]).toLowerCase().includes(search.value.toLowerCase()));
});


// Fonction pour obtenir le nom du motif depuis l'ID
const getReasonName = (reasonId) => {
    const reason = props.requestReasons.find((r) => r.id === reasonId);
    return reason ? reason.reason_title : 'Inconnu';
};

// Gestion des tags de progression
const getProgressionTag = (progression) => {
    switch (progression) {
        case 'pending':
            return 'warning';
        case 'in_progress':
            return 'info';
        case 'completed':
            return 'success';
        case 'rejected':
            return 'danger';
        default:
            return '';
    }
};

const formatProgression = (progression) => {
    switch (progression) {
        case 'pending':
            return 'En attente';
        case 'in_progress':
            return 'En cours';
        case 'completed':
            return 'Complété';
        case 'rejected':
            return 'Rejeté';
        default:
            return '';
    }
};

const downloadDocuments = (documents) => {
    console.log('Télécharger les fichiers:', documents);
};

// Ouvrir la modale avec les données de la demande sélectionnée
const openEditModal = (request) => {
    selectedRequest.value = { ...request };
    editDialogVisible.value = true;
};

const handleRequest = async (request) => {
    console.log(request)
    const loadingInstance = ElLoading.service({
        text: 'Chargement des demandes...',
    });
    try {
        await ElMessageBox.confirm(
            'Êtes-vous sûr de vouloir prendre en charge cette demande ? Cette action est irréversible.',
            'Confirmation',
            {
                confirmButtonText: 'Traiter la demande',
                cancelButtonText: 'Annuler',
                type: 'warning',
            }
        );


        await requestStore.takeChargeRequest(request.id);
        await requestStore.fetchUserRequests(true);
        requestList.value = requestStore.requests;
        ElMessage.success('Demande prise en charge avec succès');
    } catch (error) {
        ElMessage.error("L'action n'a pas pû être exécuté");
    }
    loadingInstance.close();

};

const openAcceptDialog = async (request) => {
    selectedRequest.value = { ...request };
    acceptDialogVisible.value = true;
};

const acceptRequest = async () => {
    const formData = new FormData();
    formData.append("requestId", selectedRequest.value.id);
    formData.append("description", descriptionResponse.value);

    responseFileList.value.forEach((file, index) => {
        formData.append(`files[${index}]`, file.raw);
    });

    const loadingInstance = ElLoading.service({
        text: 'Chargement des demandes...',
    });

    try {
        console.log(formData);
        await requestStore.acceptRequest(formData);
        await requestStore.fetchUserRequests(true);
        requestList.value = requestStore.requests;
        acceptDialogVisible.value = false;
        ElMessage.success('Demande acceptée avec succès');
    } catch (error) {
        ElMessage.error("L'action n'a pas pû être exécuté");
    }
    loadingInstance.close();
};

const rejectRequest = async (request) => {
    console.log(request)
    const loadingInstance = ElLoading.service({
        text: 'Chargement des demandes...',
    });
    try {
        await ElMessageBox.confirm(
            'Êtes-vous sûr de vouloir refuser cette demande ? Cette action est irréversible.',
            'Confirmation',
            {
                confirmButtonText: 'Oui, refuser',
                cancelButtonText: 'Annuler',
                type: 'warning',
            }
        );

        await requestStore.rejectRequest(request.id);
        await requestStore.fetchUserRequests(true);
        requestList.value = requestStore.requests;
        ElMessage.success('Demande refusée avec succès');
    } catch (error) {
        ElMessage.error("L'action n'a pas pû être exécuté");
    }
    loadingInstance.close();
};


// Enregistrer les modifications
const updateRequest = async (requestId) => {
    const requestUpdated = {
        reason: selectedRequest.value.fields.reason[0],
        description: selectedRequest.value.fields.description
    };
    const loadingInstance = ElLoading.service({
        text: 'Chargement des demandes...',
    });
    await requestStore.updateRequest(requestId, requestUpdated)
    requestList.value = requestStore.requests
    await requestStore.fetchUserRequests(true)
    requestList.value = requestStore.requests
    loadingInstance.close();
    ElMessage({
        type: 'success',
        message: 'Demande mise à jour avec succès',
    });
    editDialogVisible.value = false;
};

const deleteRequest = async (requestId) => {
    const loadingInstance = ElLoading.service({
        text: 'Chargement des demandes...',
    });
    try {
        await ElMessageBox.confirm(
            'Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est irréversible.',
            'Confirmation',
            {
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                type: 'warning',
            }
        );

        await requestStore.deleteRequest(requestId);
        await requestStore.fetchUserRequests(true);
        requestList.value = requestStore.requests;
        ElMessage.success('Demande supprimée avec succès');
    } catch (error) {
        ElMessage.error('Erreur de suppression');
        throw error
    }
    loadingInstance.close();
};

const handleDialogClose = () => {
    acceptDialogVisible.value = false;
    descriptionResponse.value = "";
    responseFileList.value = [];
};

onMounted(async () => {
    const loadingInstance = ElLoading.service({
        text: 'Chargement des demandes...',
    });
    try {
        await requestStore.fetchUserRequests();
        if (authStore.user.role === 'Director') {
            activeTab.value = "toValidate"
        }
        requestList.value = requestStore.requests;
    } catch (error) {
        ElMessage.error('Erreur lors du chargement des demandes');
    }
    finally {
        loadingInstance.close();
    }
});

</script>
