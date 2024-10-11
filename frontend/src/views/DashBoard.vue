<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="w-full bg-white py-6 px-4 sm:px-6 border border-gray-light rounded-lg shadow-md">
      <div class="p-4">
        <h1 class="text-2xl sm:text-3xl font-bold mb-3 text-gray-medium">Bienvenue, {{ username }}</h1>
        <p class="text-sm sm:text-md text-gray-medium">Bienvenue dans votre coffre-fort numérique sécurisé.</p>
      </div>
    </div>

    <div class="mt-4 sm:mt-6 py-4 px-2 sm:px-4 bg-white shadow-md rounded-lg">
      <h2 class="text-lg sm:text-xl font-semibold text-oxford-blue mb-2">Espace de Stockage</h2>
      <div class="w-full bg-gray-light rounded-full h-4">
        <div class="bg-oxford-blue h-4 rounded-full" :style="{ width: storagePercentageUsed + '%' }">
        </div>
      </div>
      <p class="text-sm text-gray-medium mt-2">
        {{ storageRemainingGB }} GB restants sur 2 GB
      </p>
    </div>

    <div class="mt-4 sm:mt-6 rounded py-4 px-2 sm:px-4">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
        <h2 class="text-lg sm:text-xl font-semibold mb-4 sm:mb-0 flex items-center text-oxford-blue">
          <FileStack class="mr-2" />
          Mes Documents ({{ documents.length }})
        </h2>
        <!-- <button @click="openAddDocumentModal"
          class="flex items-center bg-oxford-blue hover:bg-zaffre text-white font-bold py-2 my-1 px-4 rounded-md shadow-md">
          <PlusCircleIcon class="mr-2" />
          Nouveau
        </button> -->
      </div>

      <div v-if="documents.length > 0" class="mt-4 sm:mt-6 bg-gray-light rounded py-4 px-2 sm:px-4">
        <div class="flex justify-center sm:justify-start">
          <BarChart :data="documentsByMonth" class="ml-0 sm:ml-6"></BarChart>
        </div>
      </div>

      <template v-if="!isLoadingDocuments">
        <LoaderComponent></LoaderComponent>
      </template>
      <template v-else>
        <div v-if="documents.length > 0" class="bg-white p-4 sm:p-4 rounded shadow-md mt-4 sm:mt-6">
          <h3 class="text-l font-semibold flex items-center my-4 text-oxford-blue">
            <Clock2 class="mr-2" />
            Documents récemment ajoutés
          </h3>
          <template v-if="!documentStore.areDocumentsLoaded()">
            <LoaderComponent></LoaderComponent>
          </template>
          <template v-else>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-medium">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col"
                      class="px-2 sm:px-6 py-3 text-left text-xs font-medium text-gray-medium uppercase tracking-wider">
                      Fichier
                    </th>
                    <th scope="col"
                      class="px-2 sm:px-6 py-3 text-left text-xs font-medium text-gray-medium uppercase tracking-wider">
                      Envoyé par
                    </th>
                    <th scope="col"
                      class="px-2 sm:px-6 py-3 text-left text-xs font-medium text-gray-medium uppercase tracking-wider">
                      Crée le
                    </th>
                    <th scope="col"
                      class="px-2 sm:px-6 py-3 text-xs font-medium text-gray-medium text-center uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-light">
                  <tr v-for="document in recentDocuments" :key="document.url"
                    class="hover:bg-gray-light transition-colors duration-200">
                    <td class="px-2 sm:px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <DocumentIcon :fileName="document.title" class="text-xl mr-4" />
                        <a :href="document.url" target="_blank" rel="noopener noreferrer"
                          class="text-sm font-medium text-gray-medium cursor-pointer hover:underline">
                          {{ document.title }}
                        </a>
                      </div>
                    </td>
                    <td class="px-2 sm:px-6 py-4 whitespace-nowrap">
                      <div class="text-sm flex items-center">
                        <template v-if="document.creatorPhoto">
                          <img :src="document.creatorPhoto" alt="User Photo" class="w-6 h-6 rounded-full mr-0" />
                          <div class="px-3 text-xs">{{ document.creatorName }}</div>
                        </template>
                        <template v-else>
                          <div class="flex items-center">
                            <div
                              class="w-6 h-6 rounded-full text-xs bg-gray-medium text-white flex items-center justify-center font-semibold">
                              {{ getUserInitials(document.creatorName) }}
                            </div>
                            <span class="ml-2 text-gray-medium">{{ document.creatorName }}</span>
                          </div>
                        </template>
                      </div>
                    </td>
                    <td class="px-2 sm:px-6 py-4 whitespace-nowrap flex items-center">
                      <div class="text-xs">{{ formatDateString(document.createdTime) }}</div>
                    </td>
                    <td class="px-2 sm:px-6 py-4 whitespace-nowrap text-center">
                      <div class="flex justify-center items-center space-x-4">
                        <a :href="document.url" download class="transition-colors duration-300">
                          <DownloadIcon class="w-4 h-4 text-gray-medium" />
                        </a>
                        <button @click.stop="deleteDocument(document.id)"
                          class="text-red transition-colors duration-300">
                          <Trash class="w-4 h-4 text-red hover:text-gray-medium" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="mt-4 text-center">
              <router-link to="/home/documents" class="text-gray-medium hover:underline font-semibold">
                Voir tout
              </router-link>
            </div>
          </template>
        </div>
        <div v-else class="bg-white p-4 sm:p-4 rounded shadow-md mt-4 sm:mt-6">
          Aucun document trouvé
        </div>
      </template>
    </div>

    <div class="mt-4 sm:mt-6 rounded py-4 px-2 sm:px-4">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
        <h2 class="text-lg sm:text-xl font-bold mb-4 sm:mb-0 text-oxford-blue flex items-center">
          <ClipboardList class="mr-2" />
          Mes Demandes
        </h2>
        <!-- <button @click="openMakeDemandModal"
          class="flex items-center bg-oxford-blue hover:bg-zaffre text-white font-bold py-2 my-1 px-4 rounded-md shadow-md transition-transform duration-200 ease-in-out hover:scale-105">
          <PlusCircleIcon class="mr-2" />
          Nouvelle demande
        </button> -->
      </div>

      <div class="py-0 my-2">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
          <!-- Requests List -->
          <div class="lg:col-span-2 bg-white p-4 rounded-lg shadow-md">
            <h3 class="text-l font-semibold flex items-center my-4 text-gray-medium">
              <Clock2 class="mr-2 text-oxford-blue" />
              Demandes récentes
            </h3>

            <template v-if="demandsStore.demandes.length > 0">
              <div class="mb-4">
                <CardList v-for="demande in demandsStore.demandes.slice(0, 5)" :key="demande.id" :title="demande.title"
                  :dateRequest="demande.dateRequest" :status="demande.status" :dateResponse="demande.dateResponse"
                  :demande="demande" :documentsCount="demande.documentsCount" @view-request="viewRequest(demande)" />
              </div>
              <div class="text-center mt-4">
                <router-link to="/home/requests" class="text-gray-medium hover:underline font-semibold">
                  Voir plus
                </router-link>
              </div>
            </template>

            <template v-else>
              <div class="py-5">
                <LoaderComponent v-if="demandsStore.showLoader"></LoaderComponent>
                <p v-else>Pas de demandes trouvées ...</p>
              </div>
            </template>
          </div>

          <!-- Requests Stats -->
          <div class="bg-white p-4 rounded-lg shadow-md">
            <PieChart v-if="demandsStore.demandes.length > 0" :data="documentsByStatus"></PieChart>

            <div class="p-4 my-2 text-white rounded bg-zaffre">
              <h2 class="text-lg font-semibold flex items-center">
                <Hourglass class="mr-2" />
                En cours
              </h2>
              <p class="text-2xl font-bold">{{ demandsStore.getEnCoursCount() }}</p>
            </div>

            <!-- Removed "Fermées" Section -->

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2">
              <div class="bg-green text-white p-4 rounded-lg flex items-center">
                <CircleCheckBig class="mr-2" />
                <div>
                  <h3 class="text-md font-semibold">Acceptées</h3>
                  <p class="text-lg">{{ demandsStore.getAccepteCount() }}</p>
                </div>
              </div>
              <div class="bg-red text-white p-4 rounded-lg flex items-center">
                <CircleX class="mr-2 text-white" />
                <div>
                  <h3 class="text-md font-semibold">Refusées</h3>
                  <p class="text-lg">{{ demandsStore.getRefuseCount() }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Validator Section -->
      <!-- <div class="mt-6 py-4 px-0">
        <h2 class="text-xl font-semibold mb-4 flex items-center text-gray-medium">
          <UserRound class="mr-2 my-1 w-4 h-4 text-oxford-blue" />
          Validateur
        </h2>
        <div v-if="userStore.user.role === 'Salarie'">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="validator in userStore.validators" :key="validator.id"
              class="flex items-center p-4 bg-white rounded-lg shadow-md">
              <template v-if="validator.photo">
                <img :src="validator.photo" alt="Validateur" class="w-12 h-12 rounded-full mr-4" />
              </template>
              <template v-else>
                <div
                  class="w-12 h-12 rounded-full bg-gray-medium text-white flex items-center justify-center font-semibold mx-5">
                  {{ getUserInitials(validator.name) }}
                </div>
              </template>
              <div>
                <h3 class="text-lg font-semibold text-gray-medium">{{ validator.name }}</h3>
                <p class="text-sm text-gray-500">{{ validator.role }}</p>
                <a :href="'mailto:' + validator.email" class="text-zaffre hover:underline">Contacter</a>
              </div>
            </div>
          </div>
        </div>
        <div v-if="userStore.user.role === 'Manager'">
          <p class="text-sm text-gray-medium">
            Vos demandes seront validées par un administrateur.
          </p>
        </div>
      </div> -->
    </div>


    <ModalVue :isOpen="open" title="Ajouter un document" @close="handleModalClose">
      <UploadArea @upload-complete="handleUploadComplete"></UploadArea>
    </ModalVue>
    <ModalVue :isOpen="openRequestModal" title="Nouvelle demande" @close="handleModalClose">
      <CreateRequest @close="handleModalClose" @requestCreated="demandsStore.loadDemandes"></CreateRequest>
    </ModalVue>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, provide } from 'vue'

// Components
import CardList from '@/components/CardList.vue'
import ModalVue from '@/components/ModalVue.vue'
import UploadArea from '@/components/UploadArea.vue'
import PieChart from '@/components/Charts/PieChart.vue'
import BarChart from '@/components/Charts/BarChart.vue'
import CreateRequest from '@/components/CreateRequest.vue'

// Stores
import { useDemandStore } from '@/stores/UserStore/DemandStore'
import { useDocumentStore } from '@/stores/UserStore/DocumentsStore'
import { useUserStore } from '@/stores/UserStore/UserStore'

// Utils
import { formatDateString, getUserInitials } from '@/utils'

// State and Refs
const isLoadingDocuments = ref(false)
const open = ref(false)
const openRequestModal = ref(false)

const userNom = ref('')
const userPrenom = ref('')
const username = ref('')

// Stores
const documentStore = useDocumentStore()
const demandsStore = useDemandStore()
const userStore = useUserStore()

// Computed Properties
const documents = computed(() => documentStore.getDocuments())
const documentsByMonth = computed(() => documentStore.documentsByMonth)
const documentsByStatus = computed(() => demandsStore.documentsByStatus)
const recentDocuments = computed(() => documents.value.slice(0, 5))

// Calculer l'espace utilisé et l'espace restant
const totalStorageUsed = computed(() => userStore.user.total_storage_used || 0);
const totalStorageLimit = 2 * 1024 * 1024 * 1024; // 2GB
const storagePercentageUsed = computed(() => Math.min((totalStorageUsed.value / totalStorageLimit) * 100, 100)); // Pourcentage utilisé
const storageRemainingGB = computed(() => ((totalStorageLimit - totalStorageUsed.value) / (1024 * 1024 * 1024)).toFixed(2)); // Espace restant en GB


// Initializations
const token = localStorage.getItem('jwt_token')
const userData = JSON.parse(localStorage.getItem('user_data') || '{}')

// Set user data
userNom.value = userData.nom || ''
userPrenom.value = userData.prenom || ''
username.value = `${userNom.value} ${userPrenom.value}`



onMounted(async () => {
  try {
    isLoadingDocuments.value = false
    documentStore.documentsLoaded = false
    await demandsStore.loadDemandes()
    if (userStore.user.role !== "Salarie") {
      await userStore.fetchAllEmployees()
    }
    if (!documentStore.areDocumentsLoaded()) {
      await documentStore.fetchDocuments(token)
    }
  } catch (error) {
    console.error('Erreur lors du chargement des données:', error)
  } finally {
    console.log(documentStore.allDocuments)
    isLoadingDocuments.value = true
  }
})

/* const openAddDocumentModal = () => {
  open.value = true
} */

const openMakeDemandModal = () => {
  openRequestModal.value = true
}

const handleUploadComplete = async () => {
  await documentStore.refreshDocuments(token) // Refresh documents after upload
  open.value = false
}

const deleteDocument = async (documentId) => {
  const confirmed = confirm('Êtes-vous sûr de vouloir archiver ce document ?')
  if (confirmed) {
    try {
      await documentStore.deleteDocument(documentId)
    } catch (error) {
      console.error('Erreur lors de la suppression du document:', error)
    }
  }
}

const handleModalClose = () => {
  open.value = false
  openRequestModal.value = false
}

// Provide
provide('closeModal', handleModalClose)
</script>