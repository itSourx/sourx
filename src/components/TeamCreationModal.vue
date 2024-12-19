<template>
    <el-dialog title="Créer une Nouvelle Équipe" v-model="visible" :show-close="false" align-center @close="closeModal">
        <el-form :model="newTeam" :rules="rules" ref="teamForm" label-width="120px">

            <el-form-item label="Nom de l'équipe" prop="name" label-position="top">
                <el-input v-model="newTeam.name" placeholder="Entrez le nom de l'équipe" size="large"></el-input>
            </el-form-item>

            <el-form-item label="Manager" prop="manager" label-position="top">
                <el-select v-model="newTeam.manager" placeholder="Sélectionnez un manager" size="large">
                    <el-option v-for="manager in managerList" :key="manager.id"
                        :label="`${manager.first_name} ${manager.last_name}`" :value="manager.id" />
                </el-select>
            </el-form-item>

            <el-form-item label="Membres" prop="members" label-position="top">
                <el-select v-model="newTeam.members" multiple placeholder="Sélectionnez les membres" size="large">
                    <el-option v-for="user in userList" :key="user.id" :label="`${user.first_name} ${user.last_name}`"
                        :value="user.id" />
                </el-select>
            </el-form-item>
        </el-form>

        <!-- Boutons d'action -->
        <template v-slot:footer>
            <el-button @click="closeModal">Annuler</el-button>
            <el-button type="primary" @click="createTeam">Créer</el-button>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue'
import { useTeamStore } from '@/stores/teamStore'
import { useAuthStore } from '@/stores/authStore'
import { ElMessage } from 'element-plus'

interface Team {
    name: string
    manager: number | null
    members: number[]
}

const emit = defineEmits(['close', 'team-created'])
const teamStore = useTeamStore()
const userStore = useAuthStore()
const teamForm = ref(null)

const visible = ref(true)

// Nouveau modèle d'équipe
const newTeam = ref<Team>({
    name: '',
    manager: null,
    members: [],
})

// Règles de validation pour le formulaire
const rules = {
    name: [{ required: true, message: 'Le nom de l\'équipe est requis', trigger: 'blur' }],
    manager: [{ required: true, message: 'Le manager est requis', trigger: 'change' }],
    members: [{ required: true, message: 'Veuillez sélectionner au moins un membre', trigger: 'change' }],
}

// Listes des managers et utilisateurs
const managerList = ref([])
const userList = ref([])

onMounted(async () => {
    await userStore.fetchUsers()
    userList.value = userStore.users.filter(user => user.role === 'Employee')
    managerList.value = userStore.users.filter(user => user.role === 'Manager')
})

// Fonction pour créer une équipe
async function createTeam() {
    try {
        await teamForm.value.validate()
        await teamStore.createTeam(newTeam.value)
        ElMessage.success('Équipe créée avec succès !')
        emit('team-created', newTeam.value)
        closeModal()
    } catch (error) {
        console.error('Erreur lors de la création de l\'équipe:', error)
        ElMessage.error('Échec de la création de l\'équipe')
    }
}

// Fonction pour fermer le modal
function closeModal() {
    emit('close')
    resetForm()
}

function resetForm() {
    newTeam.value = { name: '', manager: null, members: [] }
}
</script>

<style scoped>
.dialog-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}
</style>