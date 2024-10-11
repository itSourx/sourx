<template>
  <div class="max-w-7xl mx-auto min-h-screen">
    <div class="p-6 rounded-lg">
      <h1 class="text-2xl text-night mb-4">Tableau de Bord</h1>
      <p class="text-md text-gray-medium mb-8">
        Statistiques de l'application et gestion des documents.
      </p>

      <NotificationComponent v-if="pendingRequests > 0" />

      <!-- Overview Section -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
        <div class="bg-zaffre text-white p-6 rounded-lg shadow-md flex items-center">
          <UserRound class="w-8 h-8 mr-6" />
          <div>
            <h2 class="text-xl">Utilisateurs</h2>
            <p class="text-3xl font-bold">{{ totalUsers }}</p>
            <router-link to="/management/administration" class="hover:underline">Gérer les Utilisateurs</router-link>
          </div>
        </div>
        <div class="bg-yellow text-night p-6 rounded-lg shadow-md flex items-center">
          <ClipboardList class="text-xl text-night mr-6" />
          <div>
            <h2 class="text-xl">Équipes</h2>
            <p class="text-3xl font-bold">{{ totalTeams }}</p>
            <router-link to="/management/administration" class="hover:underline">Gérer les Équipes</router-link>
          </div>
        </div>
        <div class="p-6 rounded-lg shadow-md flex items-center text-green">
          <UserRound class="w-8 h-8 mr-6" />
          <div>
            <h2 class="text-xl">Documents</h2>
            <p class="text-3xl font-bold text-green">{{ totalDocuments }}</p>
            <router-link to="/management/documents" class="hover:underline">Gérer les Documents</router-link>
          </div>
        </div>
        <div class="bg-zaffre text-white p-6 rounded-lg shadow-md flex items-center">
          <Hourglass class="w-8 h-8 mr-6" />
          <div>
            <h2 class="text-xl">Demandes à valider</h2>
            <p class="text-3xl font-bold text-white">{{ pendingRequests }}</p>
            <router-link to="/home/requests" class="hover:underline">Gérer les demandes</router-link>
          </div>
        </div>
      </div>

      <!-- Storage Details Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
          <h2 class="text-xl font-bold mb-4">Stockage</h2>
          <div class="flex items-center justify-between">
            <div class="w-1/2">
              <PieChart :data="storageDetails" />
            </div>
            <div class="w-1/2">
              <div class="flex flex-col items-center">
                <div class="text-3xl font-bold text-gray-medium">
                  {{ usedStorage }} / {{ totalStorage }} GB
                </div>
                <div class="mt-4 flex flex-col space-y-2">
                  <div class="flex items-center">
                    <div class="w-3 h-3 bg-zaffre rounded-full mr-2"></div>
                    <div>Documents : {{ documentStorage }} GB</div>
                  </div>
                  <div class="flex items-center">
                    <div class="w-3 h-3 bg-green rounded-full mr-2"></div>
                    <div>Médias : {{ mediaStorage }} GB</div>
                  </div>
                  <div class="flex items-center">
                    <div class="w-3 h-3 bg-yellow rounded-full mr-2"></div>
                    <div>Autres : {{ otherStorage }} GB</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Documents Section -->
        <div class="bg-white p-6 rounded-lg shadow-md">
          <h2 class="text-xl font-bold mb-4">Documents Récemment Ajoutés</h2>

          <LoaderComponent v-if="isLoading" />

          <div v-else-if="recentDocuments.length > 0" class="space-y-4">
            <div v-for="document in recentDocuments" :key="document.id"
              class="flex justify-between items-center p-4 rounded-md border border-gray-light hover:bg-gray-50 transition-colors duration-200">
              <div class="flex items-center">
                <DocumentIcon :fileName="document.name" class="text-xl mr-4" />
                <div>
                  <a :href="document.url" download
                    class="text-sm font-medium text-zaffre hover:underline hover:text-oxford-blue transition-colors duration-200">
                    {{ document.name }}
                  </a>
                  <p class="text-xs text-gray-500">
                    Ajouté le {{ document.dateAdded }}
                  </p>
                </div>
              </div>
              <div class="flex items-center space-x-4">
                <a :href="document.url" download class="text-zaffre hover:underline">
                  <DownloadIcon class="w-4 h-4" />
                </a>
                <button @click="deleteDocument(document.id)" class="text-red hover:underline">
                  <Trash class="w-4 h-4 hover:text-gray-medium transition-colors duration-200" />
                </button>
              </div>
            </div>
          </div>

          <div v-else class="text-sm text-gray-light">Aucun document récent ajouté...</div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import BarChart from '@/components/Charts/BarChart.vue'
import PieChart from '@/components/Charts/PieChart.vue'
import LoaderComponent from '@/components/LoaderComponent.vue';
import NotificationComponent from '@/components/NotificationComponent.vue'
import { useAdminStore } from '@/stores/AdminStore/AdminStore'
import { useUserManagementStore } from '@/stores/AdminStore/UserManagementStore'
import { useAdminDocumentStore } from '@/stores/AdminStore/DocumentsManagementStore'
import { useDemandStore } from '@/stores/UserStore/DemandStore'


const isLoading = ref(true);
const adminStore = useAdminStore()
const userStore = useUserManagementStore()
const documentStore = useAdminDocumentStore()
const demandsStore = useDemandStore()
const token = localStorage.getItem('jwt_token')

const totalUsers = computed(() => userStore.users.length)
const totalDocuments = computed(() => {
  const filesOutsideFolders = documentStore.files.length;
  const filesInsideFolders = documentStore.folders.reduce((total, folder) => {
    return total + (folder.documents ? folder.documents.length : 0);
  }, 0);

  return filesOutsideFolders + filesInsideFolders;
});
const totalTeams = computed(() => {
  const uniqueTeams = new Set(
    userStore.users.flatMap(user => user.Equipe || [])
  );
  return uniqueTeams.size;
});

const pendingRequests = computed(() => demandsStore.demandesToBeValidated.length);

const recentDocuments = computed(() => {
  // Combine les fichiers hors dossiers et les fichiers dans les dossiers
  const allDocuments = [
    /* ...documentStore.files, */
    ...documentStore.folders.flatMap(folder => folder.documents)
  ];
  // Trier les documents par date d'ajout, du plus récent au plus ancien
  const sortedDocuments = allDocuments.sort((a, b) => {
    return new Date(b.dateAdded) - new Date(a.dateAdded);
  });

  // Retourner les 5 premiers documents
  return sortedDocuments.slice(0, 5);
});
const storageDetails = computed(() => adminStore.storageDetails)
const usedStorage = computed(() => adminStore.usedStorage)
const totalStorage = computed(() => adminStore.totalStorage)
const documentStorage = computed(() => adminStore.documentStorage)
const mediaStorage = computed(() => adminStore.mediaStorage)
const otherStorage = computed(() => adminStore.otherStorage)

onMounted(async () => {
  isLoading.value = true;
  await userStore.fetchAllUsers()
  await documentStore.fetchFoldersAndFiles(token)
  await demandsStore.loadDemandesToBeValidated();
  isLoading.value = false;
})

const deleteDocument = (documentId) => {
  const confirmed = confirm('Êtes-vous sûr de vouloir supprimer ce document ?')
  console.log(documentId)

  if (confirmed) {
    documentStore.removeFile(documentId)
  }
}
</script>
