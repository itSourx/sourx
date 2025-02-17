<template>
    <el-dialog title="Modifier l'Équipe" v-model="visible" width="500px" @close="closeModal">
        <el-form :model="teamData" :rules="rules" ref="teamForm" label-width="120px">
            <!-- Nom de l'équipe -->
            <el-form-item label="Nom" prop="name">
                <el-input v-model="teamData.name" placeholder="Entrez le nom de l'équipe"></el-input>
            </el-form-item>

            <!-- Manager de l'équipe -->
            <el-form-item label="Manager" prop="manager">
                <el-select v-model="teamData.manager" placeholder="Sélectionnez un manager">
                    <el-option v-for="manager in managerList" :key="manager.id"
                        :label="`${manager.first_name} ${manager.last_name}`" :value="manager.id" />
                </el-select>
            </el-form-item>

            <!-- Membres de l'équipe -->
            <el-form-item label="Membres" prop="members">
                <el-select v-model="teamData.members" multiple placeholder="Sélectionnez les membres">
                    <el-option v-for="user in userList" :key="user.id" :label="`${user.first_name} ${user.last_name}`"
                        :value="user.id" />
                </el-select>
            </el-form-item>
        </el-form>

        <template #footer>
            <el-button @click="closeModal">Annuler</el-button>
            <el-button type="primary" @click="updateTeam">Sauvegarder</el-button>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, onMounted, watch } from 'vue'
import { useTeamStore } from '@/stores/teamStore'
import { useAuthStore } from '@/stores/authStore'
import { ElMessage } from 'element-plus'
import { nextTick } from 'vue'

interface Team {
    id: string
    name: string
    manager: number | null
    members: number[]
}

const props = defineProps({
    teamToEdit: {
        type: Object as () => Team,
        required: true,
    }
})

const emit = defineEmits(['close', 'team-updated'])
const teamStore = useTeamStore()
const userStore = useAuthStore()
const visible = ref(true)

// Données de l'équipe à modifier
const teamData = ref<Team>({
    id: '',
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
const teamForm = ref(null)

onMounted(async () => {
    if (!userStore.users.length) {
        await userStore.fetchUsers()
    }
    userList.value = userStore.users.filter(user => user.role === 'Employee')
    managerList.value = userStore.users.filter(user => user.role === 'Manager')

    if (props.teamToEdit) {
        populateTeamData()
    }
})

// Remplir les données de l'équipe
function populateTeamData() {
    if (props.teamToEdit) {
        Object.assign(teamData.value, {
            id: props.teamToEdit.id,
            name: props.teamToEdit.fields.name,
            manager: props.teamToEdit.fields.manager?.[0] ?? null,
            members: props.teamToEdit.fields.users ?? [],
        })

    }
}

// Fonction pour mettre à jour l'équipe
async function updateTeam() {
    try {
        await teamForm.value.validate()
        await teamStore.updateTeam(teamData.value.id, teamData.value)
        ElMessage.success('Équipe mise à jour avec succès !')
        emit('team-updated', teamData.value)
        closeModal()
    } catch (error) {
        console.error('Erreur lors de la mise à jour de l\'équipe:', error)
        ElMessage.error('Échec de la mise à jour de l\'équipe')
    }
}

// Fonction pour fermer le modal
function closeModal() {
    emit('close')
    resetForm()
}

// Réinitialiser le formulaire
function resetForm() {
    teamData.value = { id: 0, name: '', manager: null, members: [] }
}

watch(() => props.teamToEdit, async (newTeam) => {
    if (newTeam) {
        await nextTick()
        populateTeamData()
    }
})

</script>

<style scoped>
.dialog-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}
</style>