<template>
    <div class="team-management w-full max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Gestion des Équipes</h1>

        <el-button type="primary" @click="openTeamCreationModal">Créer une Nouvelle Équipe</el-button>

        <!-- Team List -->
        <el-table :data="teams" style="width: 100%" class="mt-4" empty-text="Aucune équipe">
            <el-table-column prop="fields.name" label="Nom de l'Équipe" />
            <el-table-column label="Manager" v-slot="scope">
                <span>{{ scope.row.fields['first_name (from manager)'][0] }}</span>
            </el-table-column>
            <el-table-column label="Nb membres">
                <template #default="scope">
                    <span>{{ scope.row.fields.users ? scope.row.fields.users.length : 0 }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Statut">
                <template #default="scope">
                    <div class="flex items-center space-x-2">
                        <el-switch :model-value="scope.row.fields.status === 'active'"
                            @change="toggleActive(scope.row.id, scope.row.fields.status)" active-color="#13ce66"
                            inactive-color="#ff4949" />
                        <span
                            :class="{ 'text-green-600 font-semibold': scope.row.fields.status === 'active', 'text-red-500 font-semibold': scope.row.fields.status !== 'active' }">
                            {{ scope.row.fields.status === 'active' ? 'Activée' : 'Désactivée' }}
                        </span>
                    </div>
                </template>
            </el-table-column>
            <el-table-column label="Actions">
                <template #default="scope">
                    <el-button @click="openTeamEditModal(scope.row)" size="large">
                        <el-icon>
                            <Edit />
                        </el-icon>
                    </el-button>
                </template>
            </el-table-column>
        </el-table>

        <!-- Modals for team creation and editing -->
        <TeamCreationModal v-if="showCreationModal" @close="showCreationModal = false" @team-created="addTeam" />
        <TeamEditModal v-if="showEditModal" :teamToEdit="selectedTeam" @close="showEditModal = false"
            @team-updated="updateTeam" />
    </div>
</template>

<script lang="ts" setup>
import { ref, onMounted, watch } from 'vue'
import { useTeamStore } from '@/stores/teamStore'
import { EditPen } from '@element-plus/icons-vue'
import TeamCreationModal from '@/components/TeamCreationModal.vue'
import TeamEditModal from '@/components/TeamEditModal.vue'
import { ElLoading, ElMessage } from 'element-plus'

const teamStore = useTeamStore()
const teams = ref([])
const showCreationModal = ref(false)
const showEditModal = ref(false)
const selectedTeam = ref(null)

const openTeamCreationModal = () => {
    showCreationModal.value = true
}

const openTeamEditModal = (team) => {
    selectedTeam.value = { ...team } // Cloner l'objet pour éviter des mutations lentes
    showEditModal.value = true
}

const toggleActive = async (teamId, currentStatus) => {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active'

    // Afficher le loader
    const loadingInstance = ElLoading.service({
        target: `.team-${teamId}`,
        text: 'Changement de statut...',
    })

    try {
        // Effectuer le changement de statut
        await teamStore.updateTeamStatus(teamId, newStatus)
        ElMessage.success(`Statut de l'équipe mis à jour avec succès !`)
        await teamStore.fetchTeams(true)
        teams.value = teamStore.teams
    } catch (error) {
        ElMessage.error("Erreur lors de la mise à jour du statut de l'équipe")
    } finally {
        // Fermer le loader après l'opération
        loadingInstance.close()
    }
}

const addTeam = async newTeam => {
    await teamStore.fetchTeams(true)
}

const updateTeam = async updatedTeam => {
    await teamStore.fetchTeams(true)
    teams.value = teamStore.teams
}

// Récupérer les équipes et mettre à jour la variable `teams`
onMounted(async () => {
    await teamStore.fetchTeams()
    teams.value = teamStore.teams
})

// Mettre à jour la variable `teams` lorsque `teamStore.teams` change
watch(
    () => teamStore.teams,
    newTeams => {
        teams.value = newTeams
    }
)
</script>

<style scoped>
.team-management {
    padding: 1rem;
}

.el-table__row {
    transition: background-color 0.3s ease;
}

.el-table__row.is-loading {
    background-color: #f5f5f5;
}
</style>
