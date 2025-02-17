import { computed } from 'vue';
import { useCompanyStore } from '@/stores/companyStore';

const companyStore = useCompanyStore();

export const editableFields = [
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

export const updateEditableFields = () => {
    editableFields.forEach(field => {
        if (field.id === 'companyName') field.defaultText = companyStore.companyInfo?.fields.name || 'Nom de l\'Entreprise';
        if (field.id === 'companyAddress') field.defaultText = companyStore.companyInfo?.fields.address || 'Adresse de l\'Entreprise';
        if (field.id === 'companyPhone') field.defaultText = companyStore.companyInfo?.fields.phone || 'Numéro de Téléphone';
        if (field.id === 'companyNameRef') field.defaultText = companyStore.companyInfo?.fields.name || 'Nom de l\'Entreprise';
        if (field.id === 'location') field.defaultText = companyStore.companyInfo?.fields.address || 'Adresse de l\'Entreprise';
    });
};
