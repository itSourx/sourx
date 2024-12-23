<template>
    <div class="user-management-table w-full overflow-x-auto my-3 max-w-7xl mx-auto mt-4">
        <!-- Button to create a new user -->
        <div class="flex justify-between items-center mb-4">
            <el-button type="primary" size="large" @click="openUserCreationModal">
                Créer un Nouveau Utilisateur
            </el-button>
            <el-input v-model="search" size="large" placeholder="Rechercher par nom ou email" clearable
                :prefix-icon="Search" class="w-1/2" />
        </div>

        <!-- Table for users -->
        <el-table :data="filteredUsers" style="width: 100%" empty-text="Pas d'utilisateur">
            <el-table-column prop="fullName" label="Nom & Prénom">
                <template #default="scope">
                    <div class="flex items-center">
                        <span> {{ scope.row['last_name'] }} {{ scope.row['first_name'] }} </span>
                    </div>
                </template>
            </el-table-column>
            <el-table-column prop="email" label="Email" />
            <el-table-column prop="role" label="Rôle" sortable>
                <template #default="scope">
                    <el-tag :type="getRoleColor(scope.row.role)" effect="dark">
                        {{ scope.row.role }}
                    </el-tag>
                </template>
            </el-table-column>
            <el-table-column prop="createdAt" label="Date de Création" sortable>
                <template #default="scope">
                    <div class="flex items-center">
                        <span> {{ formatDate(scope.row['created_at']) }} </span>
                    </div>
                </template>
            </el-table-column>
            <el-table-column label="Statut">
                <template #default="scope">
                    <el-switch v-model="scope.row.status" active-text="Actif" inactive-text="Archivé"
                        @change="toggleUserStatus(scope.row)" :disabled="scope.row.isLoading" />
                </template>
            </el-table-column>
            <el-table-column label="Actions">
                <template #default="scope">
                    <el-button size="large" @click.stop="openUserDetailModal(scope.row)">
                        <el-icon class="cursor-pointer">
                            <EditPen />
                        </el-icon>
                    </el-button>
                </template>
            </el-table-column>
        </el-table>

        <!-- Modals -->
        <UserDetailModal v-if="showDetailModal" :user="selectedUser" @close="closeUserDetailModal"
            @user-updated="handleUserUpdated" />
        <UserCreationModal v-if="showCreationModal" @close="showCreationModal = false" @user-created="addUser" />
    </div>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted } from 'vue';
import { Search, User } from '@element-plus/icons-vue';
import UserDetailModal from '@/components/UserDetailModal.vue';
import UserCreationModal from '@/components/UserCreationModal.vue';
import { useAuthStore } from '@/stores/authStore';
import { ElMessage, ElLoading } from 'element-plus';

const userStore = useAuthStore();
const search = ref('');
const selectedUser = ref(null);
const showDetailModal = ref(false);
const showCreationModal = ref(false);
const usersList = ref([])

const filteredUsers = computed(() => {
    return usersList.value
        .map(user => ({
            ...user,
            fullName: `${user.firstName} ${user.lastName}`,
            status: isActive(user.status)
        }))
        .filter(user =>
            user.fullName.toLowerCase().includes(search.value.toLowerCase()) ||
            user.email.toLowerCase().includes(search.value.toLowerCase())
        );
});

const openUserDetailModal = (user) => {
    selectedUser.value = user;
    showDetailModal.value = true;
};

const closeUserDetailModal = () => {
    showDetailModal.value = false;
    selectedUser.value = null;
};

const toggleUserStatus = async (user) => {
    const loadingInstance = ElLoading.service({
        target: `.team-${user.id}`,
        text: 'Changement de statut...',
    });
    user.isLoading = true;

    try {
        await userStore.archiveUser(user.id)
        await userStore.fetchUsers()
        usersList.value = userStore.users.map(u => ({
            ...u,
            isLoading: false,
        }));
        ElMessage.success('Statut de l\'utilisateur mis à jour avec succès');
    } catch (error) {
        console.error("Erreur lors de la mise à jour du statut de l'utilisateur:", error)
        user.isLoading = false;
        ElMessage.error('Erreur lors de la mise à jour du statut de l\'utilisateur');
    } finally {
        loadingInstance.close();
    }
}

const openUserCreationModal = () => {
    showCreationModal.value = true;
};

const addUser = async (newUser) => {
    const loadingInstance = ElLoading.service({
        text: 'Ajout de l\'utilisateur en cours...',
    });

    try {
        await userStore.fetchUsers();
        usersList.value = userStore.users.map(user => ({
            ...user,
            isLoading: false,
        }));

        showCreationModal.value = false;
        ElMessage.success('Utilisateur ajouté avec succès');
    } catch (error) {
        console.error("Erreur lors de l'ajout de l'utilisateur:", error);
        ElMessage.error('Erreur lors de l\'ajout de l\'utilisateur');
    } finally {
        loadingInstance.close(); // Ferme l'indicateur de chargement
    }
};


const handleUserUpdated = (updatedUser) => {
    const index = usersList.value.findIndex(user => user.id === updatedUser.id);
    if (index !== -1) {
        usersList.value[index] = { ...usersList.value[index], ...updatedUser };
        ElMessage.success('Utilisateur mis à jour avec succès');
    } else {
        console.error("Utilisateur introuvable pour la mise à jour");
    }
};

function formatDate(date) {
    const options = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(date).toLocaleDateString('fr-FR', options).replace(',', ' à');
}

const getRoleColor = (role) => {
    switch (role) {
        case 'Employee':
            return 'success';
        case 'Manager':
            return 'warning';
        case 'Director':
            return 'danger';
        default:
            return 'info'; // Couleur par défaut si le rôle n'est pas reconnu
    }
};

function isActive(status) {
    return status === 'active';
};

onMounted(async () => {
    const loadingInstance = ElLoading.service({
        text: 'Chargement des utilisateurs...',
    });

    try {
        await userStore.fetchUsers();
        usersList.value = userStore.users.map(user => ({
            ...user,
            isLoading: false,
        }));
    } catch (error) {
        console.error("Erreur lors de la récupération des utilisateurs:", error);
        ElMessage.error('Erreur lors du chargement des utilisateurs');
    } finally {
        loadingInstance.close();  // Close the loader after data is fetched
    }
});
</script>

<style scoped>
.user-management-table .el-input {
    max-width: 300px;
}
</style>
