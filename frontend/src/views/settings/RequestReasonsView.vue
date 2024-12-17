<template>
    <div class="max-w-7xl mx-auto mt-4">
        <div class="flex justify-between items-center my-8">
            <h2 class="text-2xl font-semibold">Gestion des Motifs de Demandes</h2>
            <el-button type="primary" @click="openDialog()">Ajouter un Motif</el-button>
        </div>

        <el-table :data="requestReasons" style="width: 100%" empty-text="Pas de demandes">
            <el-table-column prop="reason_title" label="Nom du Motif" width="200" />
            <el-table-column label="Modèle PDF">
                <template #default="scope">
                    <span>{{ scope.row.pdf_model ? 'Oui' : 'Non' }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Archivé">
                <template #default="scope">
                    <div class="flex items-center">
                        <el-switch v-model="scope.row.status" :loading="scope.row.isLoading"
                            @change="toggleStatus(scope.row)" />
                        <!-- Vous pouvez ajouter un indicateur personnalisé ici si nécessaire -->
                    </div>
                </template>
            </el-table-column>
            <el-table-column label="Actions">
                <template #default="scope">
                    <el-button size="large" @click="openDialog(scope.row)">
                        <el-icon>
                            <Edit />
                        </el-icon>
                    </el-button>
                </template>
            </el-table-column>
        </el-table>

        <!-- Dialog pour Ajouter/Modifier un Motif -->
        <el-dialog :title="isEditing ? 'Modifier le Motif' : 'Ajouter un Motif'" v-model="dialogVisible">
            <el-form :model="form">
                <el-form-item label="Nom du Motif" required label-position="top">
                    <el-input v-model="form.reason_title" placeholder="Nom du motif" size="large" />
                </el-form-item>
                <QuillEditor v-model:content="form.pdf_model" contentType="text" theme="snow" />
                <el-form-item class="my-2">
                    <el-button type="primary" @click="saveRequestReason">Enregistrer</el-button>
                    <el-button @click="dialogVisible = false">Annuler</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { ElButton, ElTable, ElTableColumn, ElDialog, ElForm, ElFormItem, ElInput, ElSwitch, ElMessage, ElLoading } from 'element-plus'
import { useRequestReasons } from '@/stores/requestReasonsStore'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const store = useRequestReasons()
const requestReasons = ref([])
const dialogVisible = ref(false)
const isEditing = ref(false)
const form = ref({
    id: null,
    reason_title: '',
    pdf_model: '',
    status: false,
})

// Charger les motifs lors de l'initialisation du composant
onMounted(async () => {
    await store.fetchRequestReasons()
    requestReasons.value = store.requestReasons
})

// Ouvrir le dialog pour ajouter ou modifier un motif
const openDialog = (reason = null) => {
    if (reason) {
        isEditing.value = true
        form.value = { ...reason }
    } else {
        isEditing.value = false
        form.value = { reason_title: '', pdf_model: '', status: false }
    }
    dialogVisible.value = true
}

// Sauvegarder ou mettre à jour un motif de demande
const saveRequestReason = async () => {
    try {
        if (isEditing.value) {
            await store.updateRequestReason(form.value)
            ElMessage.success('Motif de demande mis à jour avec succès')
        } else {
            await store.addRequestReason(form.value)
            ElMessage.success('Motif de demande ajouté avec succès')
        }
        dialogVisible.value = false
        await store.fetchRequestReasons()
        requestReasons.value = store.requestReasons
    } catch (error) {
        ElMessage.error("Une erreur est survenue lors de l'enregistrement du motif de demande")
    }
}

// Archiver ou désarchiver un motif
const toggleStatus = async (reason) => {
    reason.isLoading = true // Mettre à jour l'état de chargement de la ligne
    const loadingInstance = ElLoading.service({
        target: `#switch-${reason.id}`,
        text: 'Chargement...',
    })
    try {
        reason.status = !reason.status
        await store.updateRequestReason(reason)
        const message = reason.status ? 'Motif activé avec succès' : 'Motif désactivé avec succès'
        ElMessage.success(message)
        await store.fetchRequestReasons()
        requestReasons.value = store.requestReasons
    } catch (error) {
        ElMessage.error("Une erreur est survenue lors de l'archivage du motif")
    } finally {
        reason.isLoading = false // Arrêter le chargement une fois l'opération terminée
        loadingInstance.close() // Fermer le loader une fois l'opération terminée
    }
}
</script>

<style scoped>
.text-2xl {
    font-size: 1.5rem;
}

.el-table__row.is-loading {
    background-color: #f7f7f7;
    /* Gris clair pendant le chargement */
}
</style>
