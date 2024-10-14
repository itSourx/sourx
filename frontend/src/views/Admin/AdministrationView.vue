<template>
    <div class="max-w-7xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Gestion du système</h1>

        <!-- Onglets pour basculer entre les sections -->
        <div class="flex space-x-4 mb-6 border-b border-gray-light pb-4">
            <button @click="currentSection = 'teams'" :class="{
                'bg-oxford-blue text-white shadow-md border border-oxford-blue': currentSection === 'teams',
                'border border-gray-light': currentSection !== 'teams'
            }" class="py-4 px-6 rounded-md flex items-center space-x-2 transition-colors duration-300">
                <Users class="w-5 h-5" />
                <span>Gestion des Équipes ( {{ computed(() => userStore.teams.length) }} )</span>
            </button>

            <button @click="currentSection = 'users'" :class="{
                'bg-oxford-blue text-white shadow-md border border-oxford-blue': currentSection === 'users',
                'border border-gray-light': currentSection !== 'users'
            }" class="py-4 px-6 rounded-md flex items-center space-x-2 transition-colors duration-300">
                <User class="w-5 h-5" />
                <span>Gestion des Utilisateurs ( {{ computed(() => {
                    return userStore.users.filter(user => user.role !== 'Administrateur').length;
                }) }} )</span>
            </button>

            <button @click="currentSection = 'motifs'" :class="{
                'bg-oxford-blue text-white shadow-md border border-oxford-blue': currentSection === 'motifs',
                'border border-gray-light': currentSection !== 'motifs'
            }" class="py-4 px-6 rounded-md flex items-center space-x-2 transition-colors duration-300">
                <Notebook class="w-5 h-5" />
                <span>Motifs de demandes</span>
            </button>

            <button @click="currentSection = 'postes'" :class="{
                'bg-oxford-blue text-white shadow-md border border-oxford-blue': currentSection === 'postes',
                'border border-gray-light': currentSection !== 'postes'
            }" class="py-4 px-6 rounded-md flex items-center space-x-2 transition-colors duration-300">
                <Notebook class="w-5 h-5" />
                <span>Gestion des Postes</span>
            </button>

        </div>

        <!-- Section des Équipes -->
        <div v-if="currentSection === 'teams'">
            <div class="flex justify-between items-center mb-4">
                <button @click="openCreateTeamModal"
                    class="flex items-center bg-oxford-blue text-white py-2 px-4 rounded-md shadow-md">
                    <PlusCircleIcon class="w-4 h-4 mr-2" />
                    Créer une Équipe
                </button>
                <div class="relative">
                    <input v-model="searchQuery" type="text" placeholder="Rechercher une équipe..."
                        class="border border-gray-light text-gray-medium text-sm rounded-md px-5 py-3 pl-10 focus:outline-none" />
                    <SearchIcon class="absolute inset-y-0 left-3 p-1 my-auto text-sm text-gray-medium" />
                </div>
            </div>

            <!-- Tableau des équipes -->
            <div class="overflow-x-auto bg-white shadow-md">
                <table class="min-w-full bg-white border border-gray-light">
                    <thead class="bg-gray-light border-b">
                        <tr>
                            <th class="p-4 text-left text-sm font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
                                @click="sortBy('name')">
                                Nom de l'équipe
                                <ArrowUpNarrowWide v-if="sortKey === 'name' && sortOrder === 'asc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                                <ArrowDownNarrowWide v-if="sortKey === 'name' && sortOrder === 'desc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                            </th>
                            <th class="p-4 text-left text-sm font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
                                @click="sortBy('manager')">
                                Manager
                                <ArrowUpNarrowWide v-if="sortKey === 'manager' && sortOrder === 'asc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                                <ArrowDownNarrowWide v-if="sortKey === 'manager' && sortOrder === 'desc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                            </th>
                            <th class="p-4 text-sm font-medium text-gray-medium uppercase tracking-wider cursor-pointer text-center"
                                @click="sortBy('membersCount')">
                                Nombre de collaborateurs
                                <ArrowUpNarrowWide v-if="sortKey === 'membersCount' && sortOrder === 'asc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                                <ArrowDownNarrowWide v-if="sortKey === 'membersCount' && sortOrder === 'desc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                            </th>
                            <th class="p-4 text-left text-sm font-medium text-gray-medium uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="team in sortedTeams" :key="team.name"
                            class="bg-white text-sm border-b border-gray-light">
                            <td class="p-4 text-gray-medium">{{ team.name }}</td>
                            <td class="p-4 text-gray-medium">{{ getTeamManager(team) }}</td>
                            <td class="p-4 text-gray-medium text-center">{{ team.members.length }}</td>
                            <td class="p-4 flex space-x-2">
                                <button @click="openManageTeamModal(team)" class="text-blue hover:text-oxford-blue">
                                    <Edit3 class="w-5 h-5" />
                                </button>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" :checked="team.isArchived !== 1"
                                        @change="toggleTeamArchiveStatus(team)" />
                                    <div
                                        class="relative w-11 h-6 bg-gray-light peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-zaffre peer-checked:bg-zaffre rounded-full peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-oxford-blue after:border after:rounded-full after:h-5 after:w-5 after:transition-all">
                                    </div>
                                    <span class="ms-3 text-sm font-medium text-gray-medium">
                                        {{ team.isArchived === 1 ? 'Archivé' : 'Actif' }}
                                    </span>
                                </label>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal pour créer ou modifier une équipe -->
            <ModalVue :isOpen="isTeamModalOpen" :title="isEditing ? 'Gérer l\'équipe' : 'Créer une Équipe'"
                @close="closeTeamModal">
                <form @submit.prevent="saveTeam">
                    <div class="mb-4">
                        <label for="teamName" class="block text-gray-medium text-sm mb-2">Nom de l'équipe:</label>
                        <input v-model="form.name" type="text" id="teamName"
                            class="pl-4 mt-1 p-3 py-5 w-full rounded-md text-sm border border-gray-light" required />
                    </div>
                    <div class="mb-4">
                        <label for="teamMembers" class="block text-gray-medium text-sm mb-2">Ajouter des
                            membres:</label>
                        <MultiSelectDropdown :items="availableUsers" v-model="selectedMembers"
                            class="pl-4 mt-1 p-3 py-5 w-full rounded-md text-sm border border-gray-light" />
                    </div>
                    <div class="flex justify-end">
                        <button type="button" @click="closeTeamModal"
                            class="bg-gray-light text-gray-dark py-2 px-4 rounded-md mr-2">
                            Annuler
                        </button>
                        <button type="submit" class="bg-oxford-blue hover:bg-zaffre text-white py-2 px-4 rounded-md">
                            {{ isEditing ? 'Mettre à jour' : 'Créer' }}
                        </button>
                    </div>
                </form>
            </ModalVue>
        </div>

        <!-- Section des Utilisateurs -->
        <div v-if="currentSection === 'users'">
            <div class="flex justify-between items-center mb-4">
                <button @click="openCreateUserModal"
                    class="flex items-center bg-oxford-blue text-white py-2 px-4 rounded-md shadow-md">
                    <PlusCircleIcon class="w-4 h-4 mr-2" />
                    Ajouter un Utilisateur
                </button>
                <div class="relative">
                    <input v-model="searchQuery" type="text" placeholder="Rechercher..."
                        class="border border-gray-light text-gray-medium text-sm rounded-md px-5 py-3 pl-10 focus:outline-none" />
                    <SearchIcon class="absolute inset-y-0 left-3 p-1 my-auto text-sm text-gray-medium" />
                </div>
            </div>

            <!-- Tableau des utilisateurs -->
            <div class="overflow-x-auto bg-white shadow-md">
                <table class="min-w-full bg-white border border-gray-light">
                    <thead class="bg-gray-light border-b">
                        <tr>
                            <th class="p-4 text-left text-sm font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
                                @click="sortBy('nom')">
                                Nom
                                <ArrowUpNarrowWide v-if="sortKey === 'nom' && sortOrder === 'asc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                                <ArrowDownNarrowWide v-if="sortKey === 'nom' && sortOrder === 'desc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                            </th>
                            <th class="p-4 text-left text-sm font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
                                @click="sortBy('prenom')">
                                Prénom
                                <ArrowUpNarrowWide v-if="sortKey === 'prenom' && sortOrder === 'asc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                                <ArrowDownNarrowWide v-if="sortKey === 'prenom' && sortOrder === 'desc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                            </th>
                            <th class="p-4 text-left text-sm font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
                                @click="sortBy('email')">
                                Email
                                <ArrowUpNarrowWide v-if="sortKey === 'email' && sortOrder === 'asc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                                <ArrowDownNarrowWide v-if="sortKey === 'email' && sortOrder === 'desc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                            </th>
                            <th class="p-4 text-left text-sm font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
                                @click="sortBy('telephone')">
                                Téléphone
                                <ArrowUpNarrowWide v-if="sortKey === 'telephone' && sortOrder === 'asc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                                <ArrowDownNarrowWide v-if="sortKey === 'telephone' && sortOrder === 'desc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                            </th>
                            <th class="p-4 text-left text-sm font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
                                @click="sortBy('role')">
                                Rôle
                                <ArrowUpNarrowWide v-if="sortKey === 'role' && sortOrder === 'asc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                                <ArrowDownNarrowWide v-if="sortKey === 'role' && sortOrder === 'desc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                            </th>
                            <th class="p-4 text-left text-sm font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
                                @click="sortBy('Equipe')">
                                Équipe
                                <ArrowUpNarrowWide v-if="sortKey === 'Equipe' && sortOrder === 'asc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                                <ArrowDownNarrowWide v-if="sortKey === 'Equipe' && sortOrder === 'desc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                            </th>
                            <th class="p-4 text-left text-sm font-medium text-gray-medium uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in paginatedUsers" :key="user.id"
                            class="bg-white text-sm border-b border-gray-light">
                            <td class="p-4 text-gray-medium">{{ user.nom }}</td>
                            <td class="p-4 text-gray-medium">{{ user.prenom }}</td>
                            <td class="p-4 text-gray-medium">{{ user.email }}</td>
                            <td class="p-4 text-gray-medium">{{ user.telephone }}</td>
                            <td class="p-4 text-gray-medium">{{ user.role || '-' }}</td>
                            <td class="p-4 text-gray-medium">
                                <ul>
                                    <li v-for="equipe in (Array.isArray(user.Equipe) ? user.Equipe : user.Equipe.split(','))"
                                        :key="equipe">
                                        {{ equipe.trim() }}
                                    </li>
                                </ul>
                            </td>
                            <td class="p-4 flex space-x-2">
                                <button @click="openEditUserModal(user)" class="text-blue hover:text-oxford-blue">
                                    <Edit3 class="w-5 h-5" />
                                </button>

                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" :checked="user.isArchived !== 1"
                                        @change="toggleArchiveStatus(user)" />
                                    <div
                                        class="relative w-11 h-6 bg-gray-light peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-zaffre peer-checked:bg-zaffre rounded-full peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-oxford-blue after:border after:rounded-full after:h-5 after:w-5 after:transition-all">
                                    </div>
                                    <span class="ms-3 text-sm font-medium text-gray-medium">
                                        {{ user.isArchived === 1 ? 'Archivé' : 'Actif' }}
                                    </span>
                                </label>

                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex justify-end space-x-2">
                <button v-if="currentPage > 1" @click="prevPage" class="text-sm flex items-center">
                    <StepBack class="w-4 h-4" />
                </button>
                <span class="text-sm flex items-center">Page {{ currentPage }} sur {{ totalPages }}</span>
                <button v-if="currentPage < totalPages" @click="nextPage" class="text-sm flex items-center">
                    <StepForward class="w-4 h-4" />
                </button>
            </div>

            <!-- Modal pour créer ou modifier un utilisateur -->
            <ModalVue :isOpen="isUserModalOpen" :title="isEditing ? 'Modifier Utilisateur' : 'Ajouter un Utilisateur'"
                @close="closeUserModal">

                <div>
                    <!-- Onglets pour basculer entre les sections -->
                    <div class="flex border-b border-gray-light mb-4">
                        <button :class="{
                            'py-2 px-4 text-sm font-medium border-b-2 border-oxford-blue': activeTab === 'form',
                            'py-2 px-4 text-sm font-medium text-gray-600 border-b-2 border-transparent hover:border-gray-light': activeTab !== 'form'
                        }" @click="activeTab = 'form'">
                            {{ isEditing ? 'Modifier Utilisateur' : 'Ajouter un Utilisateur' }}
                        </button>
                        <!-- <button :class="{
                            'py-2 px-4 text-sm font-medium border-b-2 border-oxford-blue': activeTab === 'import',
                            'py-2 px-4 text-sm font-medium text-gray-600 border-b-2 border-transparent hover:border-gray-light': activeTab !== 'import'
                        }" @click="activeTab = 'import'">
                            Importer via Excel
                        </button> -->
                    </div>

                    <div v-if="activeTab === 'form'">
                        <form @submit.prevent="saveUser" class="text-night">
                            <div class="mb-4 relative">
                                <label for="nom" class="block text-sm font-medium">Nom:</label>
                                <div class="relative flex items-center">
                                    <input v-model="form.nom" type="text" id="nom"
                                        class="pl-4 mt-1 p-3 py-5 w-full rounded-md text-sm border border-gray-light"
                                        required />
                                </div>
                            </div>

                            <div class="mb-4 relative">
                                <label for="prenom" class="block text-sm font-medium">Prénom:</label>
                                <div class="relative flex items-center">
                                    <input v-model="form.prenom" type="text" id="prenom"
                                        class="pl-4 mt-1 p-3 py-5 w-full rounded-md text-sm border border-gray-light"
                                        required />
                                </div>
                            </div>

                            <div class="mb-4 relative">
                                <label for="email" class="block text-sm font-medium">Email:</label>
                                <div class="relative flex items-center">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                        <Mail class="text-gray-light w-5 h-5" />
                                    </span>
                                    <input v-model="form.email" :disabled="isEditing" type="email" id="email"
                                        class="pl-12 mt-1 p-3 py-5 w-full rounded-md text-sm border border-gray-light" :class="isEditing ? 'bg-gray-light' : ''"
                                        required />
                                </div>
                            </div>

                            <div class="mb-4 relative">
                                <label for="telephone" class="block text-sm font-medium">Téléphone:</label>
                                <div class="relative flex items-center">
                                    <vue-tel-input v-model="form.telephone" :only-countries="['FR', 'US', 'GB', 'BJ']"
                                        @blur="validatePhone(form.telephone)" placeholder="Entrez le numéro de téléphone"
                                        class="pl-4 mt-1 p-3 py-5 w-full rounded-md text-sm border border-gray-light" />
                                </div>
                            </div>

                            <div class="mb-4 relative">
                                <label for="role" class="block text-sm font-medium">Rôle:</label>
                                <div class="relative flex items-center">
                                    <select v-model="form.role" id="role"
                                        class="pl-4 mt-1 p-3 py-5 w-full rounded-md text-sm border border-gray-light bg-white"
                                        required>
                                        <option disabled value="">Sélectionnez un rôle</option>
                                        <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4 relative">
                                <label for="role" class="block text-sm font-medium">Poste:</label>
                                <div class="relative flex items-center">
                                    <select v-model="form.poste" id="poste"
                                        class="pl-4 mt-1 p-3 py-5 w-full rounded-md text-sm border border-gray-light bg-white"
                                        required>
                                        <option disabled value="">Sélectionnez un poste</option>
                                        <option v-for="poste in posteStore.postes" :key="poste.id" :value="poste.id">{{
                                            poste.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4 relative">
                                <label for="equipe" class="block text-sm font-medium">Équipe:</label>
                                <MultiSelectDropdown :items="equipeOptions" v-model="selectedEquipes"
                                    class="pl-4 mt-1 p-3 py-5 w-full rounded-md text-sm border border-gray-light" />
                            </div>

                            <div class="flex justify-end">
                                <button type="button" @click="closeUserModal"
                                    class="bg-gray-light text-gray-dark py-2 px-4 rounded-md mr-2">
                                    Annuler
                                </button>
                                <button type="submit"
                                    class="bg-oxford-blue hover:bg-zaffre text-white py-2 px-4 rounded-md">
                                    {{ isEditing ? 'Mettre à jour' : 'Ajouter' }}
                                </button>
                            </div>
                        </form>

                    </div>

                    <div v-if="activeTab === 'import'">
                    </div>
                </div>
            </ModalVue>
        </div>

        <div v-if="currentSection === 'motifs'">
            <MotifsManagement />
        </div>

        <!-- Section des Postes -->
        <div v-if="currentSection === 'postes'">
            <PosteManagement />
        </div>

    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useUserManagementStore } from '@/stores/AdminStore/UserManagementStore'
import { usePosteStore } from '@/stores/UserStore/PosteStore';
import ModalVue from '@/components/ModalVue.vue'
import MultiSelectDropdown from '@/components/MultiSelectDropdown.vue'
import MotifsManagement from '@/components/MotifsManagement.vue';
import PosteManagement from '@/components/PosteManagement.vue';

// Utilisation du store
const userStore = useUserManagementStore()
const posteStore = usePosteStore();

onMounted(async () => {
    await userStore.fetchAllUsers();
    await posteStore.fetchPostes();

});

// Variables et méthodes communes pour les deux sections
const searchQuery = ref('')
const currentSection = ref('teams')
const sortKey = ref('')
const sortOrder = ref('asc')
const activeTab = ref('form')
const isEditing = ref(false)
const form = ref({ id: null, name: '', members: [], email: '', telephone: '', nom: '', prenom: '', role: '', poste: '', Equipe: [] })
const selectedMembers = ref([])
const selectedEquipes = ref([])
const isTeamModalOpen = ref(false)
const isUserModalOpen = ref(false)

// Gestion des équipes
const teams = computed(() => userStore.teams)
const availableUsers = computed(() => {
    return userStore.users.map((user) => ({
        ...user,
        name: user.role === 'Manager' ? `${user.nom} ${user.prenom} (Manager)` : `${user.nom} ${user.prenom}`
    }))
})
const filteredTeams = computed(() => {
    return teams.value.filter((team) =>
        team.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})
const sortedTeams = computed(() => {
    return [...filteredTeams.value].sort((a, b) => {
        let result = 0
        if (sortKey.value === 'membersCount') {
            result = a.members.length - b.members.length
        } else {
            if (a[sortKey.value] < b[sortKey.value]) result = -1
            if (a[sortKey.value] > b[sortKey.value]) result = 1
        }
        return sortOrder.value === 'asc' ? result : -result
    })
})

const getTeamManager = (team) => {
    const manager = team.members.find((member) => member.role === 'Manager');
    return manager ? `${manager.nom} ${manager.prenom}` : 'Aucun Manager';
};

// Gestion des utilisateurs
const roles = computed(() => {
    const allRoles = userStore.users.map(user => user.role)
    return [...new Set(allRoles)].filter(role => role) // Supprime les doublons et les valeurs nulles ou indéfinies
})

const validatePhone = (phone) => {
    const phoneRegex = /^(\+?[\d\s-]{7,15}|0\d{9})$/;
    const isValid = phoneRegex.test(phone);

    if (!isValid) {
        alert('Numéro de téléphone invalide. Veuillez entrer un numéro valide.');
    }
};

const equipeOptions = computed(() => {
    const allEquipes = userStore.users.flatMap(user => user.Equipe || [])
    const uniqueEquipes = [...new Set(allEquipes)]
    return uniqueEquipes.map(equipe => ({
        id: equipe,
        name: equipe
    }))
})
const filteredUsers = computed(() => {
    return userStore.users.filter(
        (user) =>
            user &&
            user.role !== 'Administrateur' &&
            (user.nom.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                user.prenom.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                user.email.toLowerCase().includes(searchQuery.value.toLowerCase()))
    );
})
const sortedUsers = computed(() => {
    return [...filteredUsers.value].sort((a, b) => {
        let result = 0
        if (a[sortKey.value] < b[sortKey.value]) result = -1
        if (a[sortKey.value] > b[sortKey.value]) result = 1
        return sortOrder.value === 'asc' ? result : -result
    })
})
const currentPage = ref(1)
const itemsPerPage = 10
const totalPages = computed(() => {
    return Math.ceil(filteredUsers.value.length / itemsPerPage)
})
const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    const end = start + itemsPerPage
    return sortedUsers.value.slice(start, end)
})

// Méthodes communes
const sortBy = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortKey.value = key
        sortOrder.value = 'asc'
    }
}
const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++
    }
}
const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--
    }
}

// Méthodes pour les équipes
const openCreateTeamModal = () => {
    resetForm()
    isEditing.value = false
    isTeamModalOpen.value = true
}
const openManageTeamModal = (team) => {
    form.value = { ...team }
    selectedMembers.value = [...team.members]
    isEditing.value = true
    isTeamModalOpen.value = true
}
const saveTeam = async () => {
    if (!selectedMembers.value.some((member) => member.role === 'Manager')) {
        alert('Une équipe doit avoir au moins un Manager.')
        return
    }
    form.value.members = [...selectedMembers.value]
    if (isEditing.value) {
        await userStore.updateTeam(form.value.name, form.value)
    } else {
        await userStore.createTeam(form.value)
    }
    closeTeamModal()
}
const closeTeamModal = () => {
    isTeamModalOpen.value = false
}
const confirmDeleteTeam = async (team) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer l'équipe ${team.name} ?`)) {
        await userStore.deleteTeam(team.name)
    }
}
const resetForm = () => {
    form.value = { id: null, name: '', members: [], email: '', telephone: '', nom: '', prenom: '', role: '', poste: '', Equipe: [] }
    selectedMembers.value = []
    selectedEquipes.value = []
}

// Méthodes pour les utilisateurs
const openCreateUserModal = () => {
    resetForm()
    isEditing.value = false
    isUserModalOpen.value = true
}
const openEditUserModal = (user) => {
    form.value = { ...user }
    selectedEquipes.value = user.Equipe.map((equipe) => ({
        id: equipe,
        name: String(equipe)
    }))
    isEditing.value = true
    isUserModalOpen.value = true
}
const saveUser = async () => {
    form.value.Equipe = selectedEquipes.value.map((equipe) => equipe.name)
    if (isEditing.value) {
        await userStore.updateUser(form.value.id, form.value)
    } else {
        await userStore.createUser(form.value)
    }
    closeUserModal()
}


const toggleArchiveStatus = async (user) => {
    const newStatus = user.isArchived === 1 ? 0 : 1;
    try {
        await userStore.toggleArchiveStatus(user.id, { isArchived: newStatus });
        user.isArchived = newStatus;
    } catch (error) {
        console.error('Erreur lors de la mise à jour du statut isArchived:', error);
        alert('Une erreur est survenue lors de la mise à jour du statut de l\'utilisateur.');
    }
};

const toggleTeamArchiveStatus = async (team) => {
    const newStatus = team.isArchived === 1 ? 0 : 1;
    console.log(team)
    try {
        await userStore.toggleTeamArchiveStatus(team.name, { isArchived: newStatus });
        team.isArchived = newStatus;
    } catch (error) {
        console.error('Erreur lors de la mise à jour du statut isArchived de l\'équipe:', error);
        alert('Une erreur est survenue lors de la mise à jour du statut de l\'équipe.');
    }
};

const closeUserModal = () => {
    isUserModalOpen.value = false
}

</script>

<style>
.vue-tel-input input {
    outline: none;
    box-shadow: none;
}
</style>