<template>
    <div class="max-w-7xl mx-auto mt-6 px-4" v-loading="loading" style="width: 100%">
        <!-- Zone de salutation -->
        <div class="bg-blue-100 text-blue-800 p-8 rounded-md shadow mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Bienvenue, {{ userStore.user.first_name }} 👋</h1>
                <p class="text-sm text-blue-700">Nous sommes ravis de vous revoir aujourd'hui !</p>
            </div>
            <div class="text-gray-500 text-sm">Dernière connexion : {{ userStore.user.last_login }}</div>
        </div>

        <!-- Cartes statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div v-for="(value, key) in statistics" :key="key" :class="cardClasses[key]?.background"
                @click="navigateTo(cardClasses[key]?.route)"
                class="shadow-lg rounded-lg p-6 cursor-pointer hover:shadow-xl transform hover:scale-105 transition">

                <!-- Icône et contenu -->
                <div class="flex items-center justify-start">
                    <!-- Icône dans un cercle coloré -->
                    <div :class="['flex items-center justify-center rounded-full w-16 h-16', cardClasses[key]?.color]">
                        <component :is="cardClasses[key]?.icon" style="height: 35px;" />
                    </div>

                    <!-- Titre et valeur -->
                    <div class="ml-4 flex flex-col justify-end">
                        <div class="text-sm text-gray-500">{{ cardClasses[key]?.label }}</div>
                        <div :class="cardClasses[key]?.color" class="text-3xl font-semibold mt-2">{{ value }}</div>
                    </div>
                </div>

                <!-- ProgressBar pour l'espace utilisé -->
                <div v-if="key === 'usedSpace'" class="mt-4">
                    <el-progress :percentage="parseFloat(usedSpacePercentage)" status="success" />
                    <a target=”_blank” href="https://console.cloud.google.com/welcome/new?authuser=1&hl=en&invt=Ablizg&project=sourxdocs" class="text-blue-500 text-sm mt-2 block">Gérer l'espace</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useStatisticsStore } from '@/stores/statisticsStore';
import { useAuthStore } from '@/stores/authStore';
import { ElLoading, ElIcon, ElProgress } from 'element-plus';
import { Folder, Document, User, Tickets, CircleCheck, DataAnalysis, Search } from '@element-plus/icons-vue';

// Store pour les statistiques
const loading = ref(true);
const userStore = useAuthStore();
const statisticsStore = useStatisticsStore();

const statistics = ref({});

const router = useRouter();

// Configuration des routes, styles des cartes et icônes
const cardClasses = {
    usersCount: { label: 'Utilisateurs', color: 'text-blue-500', background: 'bg-white', route: '/settings/user-management', icon: User },
    documentsCount: { label: 'Documents', color: 'text-green-500', background: 'bg-white', route: '/documents', icon: Document },
    requestsReasons: { label: 'Motifs de demandes', color: 'text-yellow-500', background: 'bg-white', route: '/settings/request-reasons', icon: Search },
    teams: { label: 'Équipe', color: 'text-indigo-500', background: 'bg-white', route: '/settings/team-management', icon: Tickets },
    requestsCount: { label: 'Demandes à valider', color: 'text-red-500', background: 'bg-white', route: '/requests', icon: CircleCheck },
    usedSpace: { label: 'Espace utilisé en Ko', color: 'text-purple-500', background: 'bg-white', route: '/space', icon: DataAnalysis },
    foldersCount: { label: 'Dossiers', color: 'text-orange-500', background: 'bg-white', route: '/dashboard', icon: Folder },
};

// Fonction pour naviguer vers une page
const navigateTo = (path: string | undefined) => {
    if (path) router.push(path);
};

// Charger les statistiques à l'initialisation
onMounted(async () => {
    const loadingInstance = ElLoading.service({
        text: 'Chargement des statistiques...',
    });

    try {
        await statisticsStore.fetchStatistics();
        statistics.value = statisticsStore.statistics;
        console.log(statisticsStore.statistics);
    } catch (error) {
        console.error('Erreur lors du chargement des statistiques:', error);
    } finally {
        loadingInstance.close();
        loading.value = false;
    }
});

// Calcul du pourcentage pour l'espace utilisé (2 Go = 2048 Ko)
const usedSpacePercentage = computed(() => {
    const totalSpace = 2048; // en Ko
    const usedSpace = statistics.value.usedSpace || 0;
    return ((usedSpace / totalSpace) * 100).toFixed(2);
});
</script>



<style scoped>
.card-hover {
    cursor: pointer;
    transform: scale(1.05);
    transition: transform 0.3s ease-in-out;
}

.card-hover:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.card-icon {
    font-size: 2rem;
    color: #4CAF50;
}

.card-content {
    margin-top: 1rem;
}

.card-value {
    font-size: 2.25rem;
    font-weight: 600;
    color: #1E40AF;
}
</style>