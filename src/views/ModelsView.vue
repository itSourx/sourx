<template>
    <div class="max-w-7xl mx-auto mt-4">
        <el-form label-position="top" class="document-form">
            <el-form-item label="Choisissez un motif" required>
                <el-select v-model="selectedPurpose" placeholder="Sélectionnez un contexte" @change="loadTemplate"
                    size="large">
                    <el-option v-for="(purpose, index) in purposes" :key="index" :label="purpose.label"
                        :value="purpose.value" />
                </el-select>
            </el-form-item>

            <div v-if="documentContent" class="editable-document" ref="documentArea">
                <div v-html="formattedDocumentContent" />
            </div>

            <el-form-item class="py-3">
                <el-button type="primary" @click="generatePDF">Télécharger le document</el-button>
            </el-form-item>

        </el-form>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import { ElSelect, ElOption, ElForm, ElFormItem, ElButton } from 'element-plus';
import html2pdf from 'html2pdf.js';
import { useRequestReasons } from '@/stores/requestReasonsStore';
import { useCompanyStore } from '@/stores/companyStore';

const logo = computed(() => companyStore.companyInfo?.fields.logo || '@/assets/logo.png');
const signature = computed(() => companyStore.companyInfo?.fields.signature || '@/assets/signature.png');

const store = useRequestReasons();
const companyStore = useCompanyStore();
const purposes = ref([]);
const selectedPurpose = ref('');
const documentTitle = ref('');
const documentContent = ref('');

const editableFields = [
    { id: 'employeeName', defaultText: 'Nom de l\'Employé' },
    { id: 'recommenderName', defaultText: 'Nom du Recommendeur' },
    { id: 'companyName', defaultText: companyStore.companyInfo?.fields.name || 'Nom de l\'Entreprise' },
    { id: 'companyAddress', defaultText: companyStore.companyInfo?.fields.address || 'Adresse de l\'Entreprise' },
    { id: 'companyPhone', defaultText: companyStore.companyInfo?.fields.phone || 'Numéro de Téléphone' },
    { id: 'employeePosition', defaultText: 'Poste de l\'Employé' },
    { id: 'employmentDate', defaultText: 'Date d\'embauche' },
    { id: 'salaryAmount', defaultText: 'Montant en Euros' },
    { id: 'internName', defaultText: 'Nom du Stagiaire' },
    { id: 'internPosition', defaultText: 'Poste du Stagiaire' },
    { id: 'startDate', defaultText: 'Date de début' },
    { id: 'endDate', defaultText: 'Date de fin' },
    { id: 'function', defaultText: 'Poste occupé' },
    { id: 'supervisorName', defaultText: 'Nom du Référent' },
    { id: 'internSkills', defaultText: 'Domaines de Compétences' },
    { id: 'internDepartment', defaultText: 'Département' },
    { id: 'companyNameRef', defaultText: companyStore.companyInfo?.fields.name || 'Nom de l\'Entreprise' },
    { id: 'location', defaultText: companyStore.companyInfo?.fields.address || 'Adresse de l\'Entreprise' },
    {
        id: 'issueDate', defaultText: new Date().toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        })
    },
    { id: 'signatoryName', defaultText: (JSON.parse(localStorage.getItem("user"))).last_name + ' ' + (JSON.parse(localStorage.getItem("user"))).first_name },
    { id: 'leaveReason', defaultText: 'Raison du Congé' },
    { id: 'contractType', defaultText: 'Type de Contrat' },
    { id: 'contractStartDate', defaultText: 'Date de début' },
    { id: 'contractEndDate', defaultText: 'Date de fin' },
    { id: 'evaluationYear', defaultText: new Date().getFullYear() },
    { id: 'goal1', defaultText: 'Premier objectif' },
    { id: 'goal2', defaultText: 'Deuxième objectif' },
    { id: 'goal3', defaultText: 'Troisième objectif' },
    { id: 'result1', defaultText: 'Premier résultat' },
    { id: 'result2', defaultText: 'Deuxième résultat' },
    { id: 'result3', defaultText: 'Troisième résultat' },
    { id: 'strengths', defaultText: 'Points forts de l\'employé' },
    { id: 'improvements', defaultText: 'Améliorations nécessaires' },
    { id: 'futureGoal1', defaultText: 'Premier objectif futur' },
    { id: 'futureGoal2', defaultText: 'Deuxième objectif futur' },
    { id: 'futureGoal3', defaultText: 'Troisième objectif futur' },
    { id: 'representativeName', defaultText: 'Nom du Représentant' },
    { id: 'representativePosition', defaultText: 'Poste du Représentant' },
    { id: 'terminationDate', defaultText: 'Date de Rupture' },
    { id: 'warningReason', defaultText: 'Raison de l\'Avertissement' },
    { id: 'overtimeHours', defaultText: 'Nombre d\'heures supplémentaires' },
    { id: 'overtimePeriodStart', defaultText: 'Date de début des heures supplémentaires' },
    { id: 'overtimePeriodEnd', defaultText: 'Date de fin des heures supplémentaires' },
    { id: 'terminationReason', defaultText: 'Raison du licenciement' },
    { id: 'noticePeriod', defaultText: 'Durée du préavis' },
    { id: 'settlementAmount', defaultText: 'Montant du solde de tout compte' },
    { id: 'seniorityDuration', defaultText: 'Durée de l\'ancienneté' },
    { id: 'departureLocation', defaultText: 'Lieu de départ' },
    { id: 'arrivalLocation', defaultText: 'Lieu d\'arrivée' },
    { id: 'tripPurpose', defaultText: 'Objet du déplacement' },
    { id: 'departureDate', defaultText: 'Date de départ' },
    { id: 'returnDate', defaultText: 'Date de retour' },
    { id: 'trainingTitle', defaultText: 'Titre de la Formation' },
    { id: 'trainingStartDate', defaultText: 'Date de début' },
    { id: 'trainingEndDate', defaultText: 'Date de fin' },
    { id: 'approverName', defaultText: 'Nom du Responsable' },
    { id: 'approverPosition', defaultText: 'Fonction du Responsable' },
    { id: 'leaveType', defaultText: 'Type de congé (Maladie, Maternité, Paternité, Annuel, Sans solde)' },
    { id: 'leaveReason', defaultText: 'Motif du congé' },
    { id: 'authorizingPerson', defaultText: 'Nom du Responsable' },
    { id: 'authorizingPosition', defaultText: 'Fonction du Responsable' },
    { id: 'absenceStartDate', defaultText: 'Date de début de l\'absence' },
    { id: 'absenceEndDate', defaultText: 'Date de fin de l\'absence' },
];


const updateEditableFields = () => {
    editableFields.forEach(field => {
        if (field.id === 'companyName') field.defaultText = companyStore.companyInfo?.fields.name || 'Nom de l\'Entreprise';
        if (field.id === 'companyAddress') field.defaultText = companyStore.companyInfo?.fields.address || 'Adresse de l\'Entreprise';
        if (field.id === 'companyPhone') field.defaultText = companyStore.companyInfo?.fields.telephone || 'Numéro de Téléphone';
        if (field.id === 'companyNameRef') field.defaultText = companyStore.companyInfo?.fields.name || 'Nom de l\'Entreprise';
        if (field.id === 'location') field.defaultText = companyStore.companyInfo?.fields.address || 'Adresse de l\'Entreprise';
    });
};

onMounted(async () => {
    await companyStore.fetchCompanyInfo();
    await store.fetchRequestReasons();
    console.log(companyStore.companyInfo)

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


// Format the document content to allow for editable fields dynamically
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

// Function to generate PDF
const generatePDF = () => {
    const element = document.querySelector('.editable-document');
    const options = {
        margin: 1,
        filename: `${documentTitle.value}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: {
            scale: 2,
            useCORS: true,
            allowTaint: false
        },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
    };
    html2pdf().from(element).set(options).save();
};
</script>

<style scoped>
.document-form {
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.editable-document {
    padding: 10px;
    min-height: 200px;
}

.editable {
    font-size: 0.9em;
    padding: 0 3px;
}
</style>
