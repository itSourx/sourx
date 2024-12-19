<template>
    <el-dialog title="Créer un Nouvel Utilisateur" v-model="visible" :show-close="false" align-center>
        <el-form :model="newUser" :rules="formRules" ref="formRef">
            <el-form-item label="Prénom" prop="firstName" label-position="top">
                <el-input v-model="newUser.firstName" size="large" />
            </el-form-item>

            <el-form-item label="Nom" prop="lastName" label-position="top">
                <el-input v-model="newUser.lastName" size="large" />
            </el-form-item>

            <el-form-item label="Email" prop="email" label-position="top">
                <el-input v-model="newUser.email" size="large" />
            </el-form-item>

            <el-form-item label="Adresse" prop="address" label-position="top">
                <el-input v-model="newUser.address" size="large" />
            </el-form-item>

            <el-form-item label="Numéro de Téléphone" prop="phoneNumber" label-position="top">
                <vue-tel-input v-model="newUser.phoneNumber" class="w-full"></vue-tel-input>
            </el-form-item>

            <el-form-item label="Rôle" prop="role" label-position="top">
                <el-select v-model="newUser.role" placeholder="Choisissez un rôle" size="large">
                    <el-option label="Employé" value="Employé" />
                    <el-option label="Manager" value="Manager" />
                    <el-option label="Directeur" value="Director" />
                </el-select>
            </el-form-item>

            <el-form-item label="Équipe" prop="team" label-position="top">
                <el-select v-model="newUser.team" placeholder="Choisissez une équipe" multiple size="large">
                    <el-option v-for="team in teamList" :key="team.id" :label="team.fields.name" :value="team.id" />
                </el-select>
            </el-form-item>

        </el-form>

        <template #footer>
            <el-button @click="close">Annuler</el-button>
            <el-button type="primary" @click="handleSubmit">Créer</el-button>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, defineEmits, onMounted, reactive } from 'vue';
import { useTeamStore } from '@/stores/teamStore'
import { useAuthStore } from '@/stores/authStore';
import { FormInstance, ElMessage } from 'element-plus';

const emit = defineEmits();
const teamStore = useTeamStore()
const userStore = useAuthStore();

const visible = ref(true);
const teamList = ref([])

const newUser = ref({
    firstName: '',
    lastName: '',
    email: '',
    address: '',
    phoneNumber: '',
    role: '',
    team: [],
});

const formRules = reactive({
    firstName: [{ required: true, message: 'Veuillez entrer le prénom', trigger: 'blur' }],
    lastName: [{ required: true, message: 'Veuillez entrer le nom', trigger: 'blur' }],
    email: [
        { required: true, message: 'Veuillez entrer un email', trigger: 'blur' },
        { type: 'email', message: 'Email invalide', trigger: 'blur' },
    ],
    address: [{ required: true, message: 'Veuillez entrer une adresse', trigger: 'blur' }],
    phoneNumber: [{ required: true, message: 'Veuillez entrer le numéro de téléphone', trigger: 'blur' }],
    role: [{ required: true, message: 'Veuillez sélectionner un rôle', trigger: 'change' }],
    team: [{ required: true, message: 'Veuillez sélectionner au moins une équipe', trigger: 'change' }],
});

const close = () => {
    emit('close');
};

const formRef = ref<FormInstance>();

const handleSubmit = () => {
    formRef.value.validate(async (valid: boolean) => {
        if (valid) {
            try {
                await userStore.createUser(newUser.value);
                ElMessage.success('Nouveau Utilisateur ajouté');
                emit('user-created', newUser.value);
                close();
            } catch (error) {
                ElMessage.error('Erreur lors de l\'ajout de l\'utilisateur'); // Message d'erreur
                console.error(error);
            }
        }
    });
};

onMounted(async () => {
    await teamStore.fetchTeams();
    teamList.value = teamStore.teams;
    console.log(teamList.value);
});
</script>
