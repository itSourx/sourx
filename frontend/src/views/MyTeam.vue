<template>
    <div class="max-w-7xl mx-auto">

        <!-- Section Mon équipe -->
        <div v-if="currentSection === 'team'">
            <h2 class="text-xl my-2">👥 Mon équipe</h2>
            <!-- Boutons de filtrage par équipe -->
            <div v-if="teams.length > 1" class="flex space-x-4 mb-6 border-b border-gray-light pb-4">
                <button @click="selectedTeam = 'Tous'" :class="{
                    'bg-oxford-blue text-white shadow-md border border-oxford-blue': selectedTeam === 'Tous',
                    'text-gray-700 border border-gray-light': selectedTeam !== 'Tous'
                }" class="py-4 px-6 rounded-md flex items-center space-x-2 transition-colors duration-300">
                    <span>Tous</span>
                </button>

                <button v-for="team in teams" :key="team" @click="selectedTeam = team" :class="{
                    'bg-oxford-blue text-white shadow-md border border-oxford-blue': selectedTeam === team,
                    'text-gray-700 border border-gray-light': selectedTeam !== team
                }" class="py-4 px-6 rounded-md flex items-center space-x-2 transition-colors duration-300">
                    <span>{{ team }}</span>
                </button>
            </div>


            <div class="overflow-x-auto bg-white shadow-md">
                <table class="min-w-full bg-white border border-gray-light">
                    <thead class="bg-gray-light border-b">
                        <tr>
                            <th class="p-4 text-left text-xs font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
                                @click="sortBy('nom')">
                                Nom
                                <ArrowUpNarrowWide v-if="sortKey === 'nom' && sortOrder === 'asc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                                <ArrowDownNarrowWide v-if="sortKey === 'nom' && sortOrder === 'desc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                            </th>
                            <th class="p-4 text-left text-xs font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
                                @click="sortBy('email')">
                                Email
                                <ArrowUpNarrowWide v-if="sortKey === 'email' && sortOrder === 'asc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                                <ArrowDownNarrowWide v-if="sortKey === 'email' && sortOrder === 'desc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                            </th>
                            <th class="p-4 text-left text-xs font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
                                @click="sortBy('telephone')">
                                Téléphone
                                <ArrowUpNarrowWide v-if="sortKey === 'telephone' && sortOrder === 'asc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                                <ArrowDownNarrowWide v-if="sortKey === 'telephone' && sortOrder === 'desc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                            </th>
                            <th class="p-4 text-left text-xs font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
                                @click="sortBy('role')">
                                Rôle
                                <ArrowUpNarrowWide v-if="sortKey === 'role' && sortOrder === 'asc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                                <ArrowDownNarrowWide v-if="sortKey === 'role' && sortOrder === 'desc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                            </th>
                            <th class="p-4 text-left text-xs font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
                                @click="sortBy('Equipe')">
                                Équipe
                                <ArrowUpNarrowWide v-if="sortKey === 'Equipe' && sortOrder === 'asc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                                <ArrowDownNarrowWide v-if="sortKey === 'Equipe' && sortOrder === 'desc'"
                                    class="w-4 h-4 text-gray-medium inline" />
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in sortedUsers" :key="user.id"
                            class="bg-white text-sm border-b border-gray-light">
                            <td class="p-4 text-gray-medium">{{ user.name }}</td>
                            <td class="p-4 text-gray-medium">{{ user.email }}</td>
                            <td class="p-4 text-gray-medium">{{ user.telephone }}</td>
                            <td class="p-4 text-gray-medium">{{ user.role || 'Non défini' }}</td>
                            <td class="p-4 text-gray-medium">
                                <ul>
                                    <li v-for="equipe in user.Equipe" :key="equipe">{{ equipe }}</li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useUserStore } from '@/stores/UserStore/UserStore'

const userStore = useUserStore()
const allEmployees = ref(userStore.employees)
const employeesUnderManager = ref([])
const teams = ref([])
const selectedTeam = ref('Tous')

const user = JSON.parse(localStorage.getItem('user_data'))
const userEquipe = Array.isArray(user.Equipe) ? user.Equipe : Object.values(user.Equipe);

const getAllEmployees = () => {
    employeesUnderManager.value = allEmployees.value.filter(
        (employee) =>
            employee.Equipe && Array.isArray(employee.Equipe) &&
            employee.Equipe.some(equipe => userEquipe.includes(equipe)) &&
            employee.role === 'Salarie'
    )
}

const getTeams = () => {
    const managerTeams = [...new Set(employeesUnderManager.value.flatMap(emp => emp.Equipe))];
    const userTeams = userEquipe;
    teams.value = managerTeams.filter(team => userTeams.includes(team));
}

// Surveiller les changements dans les employés
watch(
    () => userStore.getEmployees(),
    (newVal) => {
        allEmployees.value = newVal
        getAllEmployees()
        getTeams()
    },
    { immediate: true }
)

onMounted(async () => {
    await userStore.fetchAllEmployees()
    getAllEmployees()
    getTeams()
})

// État et fonctions pour la section Mon équipe
const searchQuery = ref('')
const sortKey = ref('')
const sortOrder = ref('asc')

const filteredUsers = computed(() => {
    return employeesUnderManager.value.filter(user => {
        const matchesSearch = user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            user.email.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesTeam = selectedTeam.value === 'Tous' ||
            (userEquipe && userEquipe.includes(selectedTeam.value));
        return matchesSearch && matchesTeam;
    });
})

const sortedUsers = computed(() => {
    return [...filteredUsers.value].sort((a, b) => {
        let result = 0
        if (a[sortKey.value] < b[sortKey.value]) result = -1
        if (a[sortKey.value] > b[sortKey.value]) result = 1
        return sortOrder.value === 'asc' ? result : -result
    })
})

const sortBy = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortKey.value = key
        sortOrder.value = 'asc'
    }
}

// État pour contrôler quelle section est active
const currentSection = ref('team')
</script>