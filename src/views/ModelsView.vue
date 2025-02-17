<template>
    <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
        <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <el-form-item label="Choisissez un motif" label-position="top" required class="m-0">
                <el-select v-model="selectedPurpose" placeholder="Sélectionnez un contexte" @change="loadTemplate"
                    size="large" class="w-full">
                    <el-option v-for="(purpose, index) in purposes" :key="index" :label="purpose.label"
                        :value="purpose.value" />
                </el-select>
            </el-form-item>

            <el-form-item label="Format de téléchargement" label-position="top" required class="m-0">
                <el-select v-model="selectedFormat" placeholder="Format de téléchargement" size="large"
                    prefix-icon="custom-prefix-icon" class="w-full">
                    <template #prefix>
                        <img v-if="selectedFormat === 'PDF'" src="@/assets/DocumentsIcons/pdf.png" alt="PDF"
                            class="h-6 w-6 mr-2">
                        <img v-else-if="selectedFormat === 'word'" src="@/assets/DocumentsIcons/doc.png" alt="Word"
                            class="h-6 w-6 mr-2">
                    </template>
                    <el-option value="PDF">
                        <template #default>
                            <div class="flex items-center">
                                <img src="@/assets/DocumentsIcons/pdf.png" alt="PDF" class="h-6 w-6 mr-2">
                                PDF
                            </div>
                        </template>
                    </el-option>
                    <el-option value="word">
                        <template #default>
                            <div class="flex items-center">
                                <img src="@/assets/DocumentsIcons/doc.png" alt="Word" class="h-6 w-6 mr-2">
                                Word
                            </div>
                        </template>
                    </el-option>
                </el-select>
            </el-form-item>
        </div>

        <el-form label-position="top" class="document-form" v-if="documentContent">
            <div class="editable-document" ref="documentArea">
                <div v-html="formattedDocumentContent" />
            </div>

            <el-form-item class="py-3">
                <el-button type="primary" @click="downloadDocument" class="w-full sm:w-auto">Télécharger le
                    document</el-button>
            </el-form-item>
        </el-form>
        <div v-else class="document-form text-center py-10 bg-gray-100 rounded-lg">
            <p class="text-sm text-gray-600">
                Veuillez choisir un motif pour générer votre document.
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import { ElSelect, ElOption, ElForm, ElFormItem, ElButton } from 'element-plus';
import { jsPDF } from 'jspdf';
import { useRequestReasons } from '@/stores/requestReasonsStore';
import { useCompanyStore } from '@/stores/companyStore';
import { saveAs } from 'file-saver';
import { editableFields, updateEditableFields } from '@/config/editableFields';
import html2canvas from 'html2canvas';
import { useAuthStore } from '@/stores/authStore'
import axios from 'axios';

const selectedFormat = ref('PDF');
const logo = computed(() => companyStore.companyInfo?.fields.logo || '@/assets/logo.png');
const signature = computed(() => companyStore.companyInfo?.fields.signature || '@/assets/signature.png');

const store = useRequestReasons();
const companyStore = useCompanyStore();
const purposes = ref([]);
const selectedPurpose = ref('');
const documentTitle = ref('');
const documentContent = ref('');
const userStore = useAuthStore()

onMounted(async () => {
    await companyStore.fetchCompanyInfo();
    await store.fetchRequestReasons();
    purposes.value = store.requestReasons.map(reason => ({
        label: reason.pdf_model ? reason.reason_title : 'Sans modèle',
        value: reason.request_reason_id
    }));
});

watch(selectedPurpose, (newPurpose) => {
    const selectedReason = store.requestReasons.find(reason => reason.request_reason_id === newPurpose);
    if (selectedReason) {
        documentTitle.value = selectedReason.pdf_model ? selectedReason.reason_title : 'Document sans titre';
        documentContent.value = selectedReason.pdf_model ? selectedReason.pdf_model : '';
    } else {
        documentTitle.value = '';
        documentContent.value = '';
    }
});

watch(
    () => companyStore.companyInfo,
    (newInfo) => {
        if (newInfo) {
            updateEditableFields();
        }
    },
    { immediate: true }
);

const downloadDocument = () => {
    if (selectedFormat.value === 'PDF') {
        generatePDF();
    } else if (selectedFormat.value === 'word') {
        generateWordDocument();
    }
};

const generateWordDocument = () => {
    const content = document.querySelector('.editable-document').innerHTML;
    const blob = new Blob([content], { type: 'application/msword' });
    saveAs(blob, `${documentTitle.value}.doc`);
};

const formattedDocumentContent = computed(() => {
    let formattedContent = documentContent.value;
    formattedContent = formattedContent.replace(/<img src=".*?" class="logo"/g, `<img src="${logo.value}" class="logo"`);
    formattedContent = formattedContent.replace(/<img src=".*?" class="signature"/g, `<img src="${signature.value}" class="signature"`);

    editableFields.forEach(field => {
        const regex = new RegExp(`(<span id="${field.id}">.*?<\\/span>)`, 'g');
        formattedContent = formattedContent.replace(regex,
            `<span contenteditable="true" class="editable" id="${field.id}">${field.defaultText}</span>`);
    });

    return formattedContent;
});
const generatePDF = async () => {
    const element = document.querySelector('.editable-document');
    if (!element) {
        console.error('Element to convert to PDF not found.');
        return;
    }

    // Récupérer les informations dynamiques
    const employeeName = document.getElementById('employeeName')?.innerText || 'Nom de l\'Employé';
    const authorName = document.getElementById('signatoryName')?.innerText || 'Nom du Signataire';
    const dateTime = new Date().toLocaleString('fr-FR');
    const watermarkData = `Employee: ${employeeName}, Author: ${authorName}, DateTime: ${dateTime}`;

    const canvas = await html2canvas(element, { scale: 2 });
    const imgData = canvas.toDataURL('image/jpeg', 0.98);

    // Envoyer les données à crypter au backend
    let encryptedWatermark = watermarkData; // Par défaut, si l'encryption échoue
    try {
        const response = await axios.post(
            '/metadata/encrypt', // Assurez-vous que le chemin est correct selon votre route
            { metadata: watermarkData },
            {
                headers: { 
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${userStore.token}` // Assurez-vous d'avoir le token d'authentification
                }
            }
        );
        alert('d')
        console.log(response.data)
        if (response.data && response.data.encrypted_metadata) {
            encryptedWatermark = response.data.encrypted_metadata;
        }
    } catch (error) {
        console.error('Erreur lors du cryptage des métadonnées:', error);
    }

    const pdf = new jsPDF({
        unit: 'mm',
        format: 'a4',
        orientation: 'portrait'
    });

    pdf.setProperties({
        title: documentTitle.value,
        author: authorName,
        creator: 'SOURX LTD',
        keywords: encryptedWatermark,
    });

    // Ajouter l'image du document
    const imgWidth = 190; // Largeur en mm (A4 - marges)
    const imgHeight = (canvas.height * imgWidth) / canvas.width;
    pdf.addImage(imgData, 'JPEG', 10, 10, imgWidth, imgHeight);
    pdf.setFontSize(40);
    pdf.setTextColor(200, 200, 200); // Gris clair
    pdf.text('', 105, 148, { angle: -45, align: 'center' }); // Filigrane visible au centre
    pdf.save(`${documentTitle.value}.pdf`);
};

// Fonction pour vérifier les métadonnées (exemple simplifié)
const verifyPDFMetadata = (pdfFile: File) => {
    // Pour une vérification réelle, vous devez utiliser une bibliothèque côté serveur
    // comme pdf-lib ou une API pour lire les métadonnées
    console.log('Vérification des métadonnées : Simulation uniquement');
    console.log('Ouvrir le PDF dans un éditeur de métadonnées pour vérifier les données suivantes :');
    console.log(`Employee: [nom], Author: [nom], DateTime: [date et heure]`);
};
</script>