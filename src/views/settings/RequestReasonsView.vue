<template>
    <div class="max-w-7xl mx-auto mt-4">
        <div class="flex justify-between items-center my-8">
            <h2 class="text-2xl font-semibold">Gestion des Motifs de Demandes</h2>
            <div class="flex items-center">
                <el-input v-model="searchQuery" placeholder="Rechercher un motif" clearable size="large" class="w-64 mr-4">
                    <template #prefix>
                        <el-icon>
                            <Search />
                        </el-icon>
                    </template>
                </el-input>
                <el-button type="primary" @click="openDialog()" size="large">Nouveau motif</el-button>
            </div>
        </div>

        <el-table :data="filteredReasons" style="width: 100%">
            <el-table-column prop="reason_title" label="Titre" sortable />
            <el-table-column label="Section" sortable :sort-method="sortBySection">
                <template #default="scope">
                    <el-tag v-if="scope.row.section" :type="getTagType(scope.row.section)" size="large" style="font-weight: bold;">
                        {{ scope.row.section }}
                    </el-tag>
                    <span v-else>Aucune section</span>
                </template>
            </el-table-column>
            <el-table-column label="Actions">
                <template #default="scope">
                    <el-switch v-model="scope.row.status" @change="toggleStatus(scope.row)" class="mr-4" />

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
            <el-form :model="form" :rules="rules" ref="formRef">
                <el-form-item label="Titre" required label-position="top">
                    <el-input v-model="form.reason_title" placeholder="Titre" size="large" maxlength="50" />
                </el-form-item>
                <el-form-item required label="Section" label-position="top">
                    <el-select v-model="form.section" placeholder="Sélectionnez la section" size="large">
                        <el-option label="Documents relatifs à l'emploi" value="Documents relatifs à l'emploi" />
                        <el-option label="Documents relatifs aux congés et absences"
                            value="Documents relatifs aux congés et absences" />
                        <el-option label="Documents administratifs et justificatifs"
                            value="Documents administratifs et justificatifs" />
                        <el-option label="Documents relatifs à la fin de contrat"
                            value="Documents relatifs à la fin de contrat" />
                    </el-select>
                </el-form-item>
                <el-form-item required label="Modèle PDF" label-position="top">
                    <el-input v-model="form.pdf_model" type="textarea" :rows="20"
                        placeholder="Modèle PDF" />
                </el-form-item>
                <el-form-item>
                    <el-button size="large" type="primary" @click="saveRequestReason">Ajouter</el-button>
                    <el-button size="large" @click="dialogVisible = false">Annuler</el-button>
                    <el-button size="large" type="info" @click="previewDocument">Aperçu</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { ElButton, ElTable, ElTableColumn, ElDialog, ElForm, ElFormItem, ElInput, ElCard, ElSwitch, ElMessage, ElLoading } from 'element-plus'
import { useRequestReasons } from '@/stores/requestReasonsStore'
import { useAuthStore } from '@/stores/authStore'
import * as CryptoJS from 'crypto-js'

const store = useRequestReasons()
const authStore = useAuthStore()
const requestReasons = ref([])
const dialogVisible = ref(false)
const isEditing = ref(false)
const searchQuery = ref('')
const form = ref({
    id: null,
    reason_title: '',
    pdf_model: '',
    section: '',
    status: false,
    encryptedCode: '',
    encryptionKey: ''
})
const formRef = ref(null)

// Règles de validation
const rules = ref({
    reason_title: [
        { required: true, message: 'Le titre est obligatoire.', trigger: 'blur' }
    ],
    section: [
        { required: true, message: 'La section est obligatoire.', trigger: 'change' }
    ],
    pdf_model: [
        { 
            required: true, 
            message: 'Le modèle PDF est obligatoire et doit inclure un élément avec l’id signatureDocument.',
            trigger: 'blur',
            validator: (_, value, callback) => {
                if (!value) {
                    callback(new Error('Le modèle PDF est obligatoire.'))
                } else if (!value.includes('id="signatureDocument"')) {
                    callback(new Error('Le modèle PDF doit inclure un élément avec l’id signatureDocument.'))
                } else {
                    callback()
                }
            }
        }
    ]
})

// Charger les motifs
onMounted(async () => {
    await store.fetchRequestReasons()
    requestReasons.value = store.requestReasons
})

// Fonction pour générer un code crypté
const generateEncryptedCode = (userId: string) => {
    const currentDate = new Date()
    const dateStr = `${currentDate.getFullYear()}-${currentDate.getMonth() + 1}-${currentDate.getDate()} ${currentDate.getHours()}:${currentDate.getMinutes()}`
    const code = `${dateStr}-${userId}`
    const encryptionKey = CryptoJS.enc.Base64.stringify(CryptoJS.enc.Utf8.parse(Date.now().toString()))
    const encryptedCode = CryptoJS.AES.encrypt(code, encryptionKey).toString()
    return { encryptedCode, encryptionKey }
}


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

        // Validez le formulaire
        await formRef.value.validate()

        // Générer la signature
        const { encryptedCode, encryptionKey } = generateEncryptedCode(authStore.user.user_id)
        const base64EncryptedCode = btoa(encryptedCode);
        form.value.encryptedCode = encryptedCode
        form.value.encryptionKey = encryptionKey
        form.value.pdf_model = form.value.pdf_model.replace(
            '<span id="signatureDocument"></span>',
            `<span id="signatureDocument" data-encrypted-code="${base64EncryptedCode}" style="display: none;"></span>`
        );

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

const previewDocument = () => {
    const previewContent = form.value.pdf_model;
    const previewWindow = window.open('', '_blank');
    if (previewWindow) {
        previewWindow.document.open();
        previewWindow.document.write(`
            <html>
                <head>
                    <title>Aperçu du Document</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .editable-document { padding: 10px; min-height: 200px; }
                    </style>
                </head>
                <body>
                    <div class="editable-document">${previewContent}</div>
                </body>
            </html>
        `);
        previewWindow.document.close();
    } else {
        ElMessage.error("Impossible d'ouvrir une nouvelle fenêtre pour l'aperçu.");
    }
};


// Archiver ou désarchiver un motif
const toggleStatus = async (reason) => {
    const loadingInstance = ElLoading.service({
        text: 'Mise à jour du statut...'
    })

    try {
        reason.status = !reason.status
        await store.updateRequestReason(reason)
        const message = reason.status ? 'Motif activé avec succès' : 'Motif désactivé avec succès'
        ElMessage.success(message)
        await store.fetchRequestReasons()
        requestReasons.value = store.requestReasons
    } catch (error) {
        ElMessage.error("Une erreur est survenue lors du changement de statut")
    } finally {
        loadingInstance.close()
    }
}

const getTagType = (section) => {
    const sectionColors = {
        "Documents relatifs à l'emploi": "success",
        "Documents relatifs aux congés et absences": "warning",
        "Documents administratifs et justificatifs": "info",
        "Documents relatifs à la fin de contrat": "danger"
    }
    return sectionColors[section] || "default"
}

const sortBySection = (a, b) => {
    if (!a.section && !b.section) return 0
    if (!a.section) return 1
    if (!b.section) return -1
    return a.section.localeCompare(b.section)
}

// Fonction pour filtrer les motifs en fonction de la recherche
const filteredReasons = computed(() => {
    if (!searchQuery.value) return requestReasons.value
    return requestReasons.value.filter((reason) =>
        reason.reason_title.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})
</script>

<style scoped>
.text-2xl {
    font-size: 1.5rem;
}
</style>