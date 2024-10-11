<template>
  <div class="max-w-7xl mx-auto">

    <div class="flex space-x-4 mb-6 border-b border-gray-light pb-4">
      <button v-if="userStore.user.role === 'Manager' || userStore.user.role === 'Salarie'"
        @click="currentSection = 'requests'" :class="{
          'bg-oxford-blue text-white shadow-md border border-oxford-blue': currentSection === 'requests',
          'border border-gray-light': currentSection !== 'requests'
        }" class="py-4 px-6 rounded-md flex items-center space-x-2 transition-colors duration-300">
        <ClipboardList class="w-5 h-5" />
        <span>Mes Demandes</span>
      </button>

      <button v-if="userStore.user.role !== 'Salarie'" @click="currentSection = 'validation'" :class="{
        'bg-oxford-blue text-white shadow-md border border-oxford-blue': currentSection === 'validation',
        'border border-gray-light': currentSection !== 'validation'
      }" class="py-4 px-6 rounded-md flex items-center space-x-2 transition-colors duration-300">
        <CheckCircle class="w-5 h-5" />
        <span>Validation des demandes</span>
        <span v-if="demandsStore.demandesToBeValidated.length > 0" class="ml-2">
          ({{ demandsStore.demandesToBeValidated.length }} à valider)
        </span>
      </button>

    </div>

    <!-- Section Mes Demandes -->
    <div v-if="currentSection === 'requests' && userStore.user.role !== 'Administrateur'" class="flex">
      <div class="w-3/4 pr-4">
        <div class="flex justify-between border-transparent">
          <h1 class="text-2xl my-4">Mes demandes</h1>

          <button @click="open = true"
            class="flex items-center bg-oxford-blue hover:bg-zaffre text-white font-bold my-1 px-4 rounded-md shadow-md">
            <PlusCircleIcon class="mr-2" />
            Nouvelle demande
          </button>
        </div>

        <div class="flex items-center justify-between my-4">
          <div class="relative w-1/2">
            <input v-model="searchQuery" type="text" placeholder="Rechercher des demandes..."
              class="w-full py-4 px-3 pl-10 border border-gray-light rounded shadow-sm focus:ring-gray-medium" />
            <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-medium">
            </SearchIcon>
          </div>
          <div class="w-1/3">
            <div class="flex items-center">
              <Filter class="mr-2 h-4 w-4 text-oxford-blue" />
              <label for="filter" class="block text-sm font-medium text-gray-medium">Filtrer par statut</label>
            </div>
            <select v-model="selectedFilter" id="filter"
              class="mt-1 block w-full py-4 px-3 border border-gray-light bg-white rounded-md shadow-sm focus:outline-none sm:text-sm">
              <option value="all" selected>Toutes les demandes</option>
              <option value="Pending">Soumis</option>
              <option value="inProgress">En cours</option>
              <option value="Approuvée">Approuvées</option>
              <option value="Refusée">Refusées</option>
            </select>
          </div>
        </div>

        <template v-if="filteredRequests.length > 0">
          <div class="mb-4">
            <CardList v-for="demande in filteredRequests" :key="demande.id" :title="demande.title"
              :dateRequest="demande.dateRequest" :status="demande.status" :dateResponse="demande.dateResponse"
              :demande="demande" :documentsCount="demande.documentsCount" @view-request="viewRequest(demande)"
              @edit-request="showEditModal" />
          </div>

        </template>

        <template v-else>
          <div class="py-5">
            <LoaderComponent v-if="demandsStore.showLoader"></LoaderComponent>
            <p v-else>Pas de demandes trouvées ...</p>
          </div>
        </template>

        <ModalVue :isOpen="open" title="Nouvelle demande" @close="handleModalClose">
          <CreateRequest @close="handleModalClose" @requestCreated="demandsStore.loadDemandes">
          </CreateRequest>
        </ModalVue>
        <ModalVue :isOpen="openRequestModal" :title="clickRequest.title" @close="handleRequestModalClose">
          <RequestDetails :demande="clickRequest"></RequestDetails>
        </ModalVue>
      </div>

      <div class="w-1/4 pl-4">
        <div class="bg-white p-6 rounded-lg shadow-md">
          <h3 class="text-sm font-semibold flex items-center mb-4 py-4 text-oxford-blue">
            <FileText class="mr-2" />
            Documents récemment ajoutés
          </h3>
          <template v-if="!documentStore.areDocumentsLoaded()">
            <LoaderComponent></LoaderComponent>
          </template>
          <template v-else>
            <ul class="space-y-4 px-6">
              <li v-for="document in recentDocuments" :key="document.url" class="flex items-start">
                <DocumentIcon :fileName="document.title" class="text-xl mr-4" />
                <div class="flex-1">
                  <a :href="document.url" download class="text-gray-medium hover:underline text-sm font-bold">
                    {{ document.title }}
                  </a>
                  <div class="text-xs text-gray-medium">
                    Ajouté le {{ formatDateString(document.createdTime) }}
                  </div>
                  <div class="text-xs text-gray-medium">
                    Taille : {{ document.size }} Mo
                  </div>
                </div>
              </li>
            </ul>
            <div class="mt-6 text-center">
              <router-link to="/home/documents" class="text-gray-medium hover:underline font-semibold">
                Voir tous les documents
              </router-link>
            </div>
          </template>
        </div>
      </div>
    </div>


    <div v-if="currentSection === 'validation' && userStore.user.role !== 'Salarie'">
      <h2 class="text-xl my-4">Validation des demandes</h2>
      <div v-if="loading" class="flex justify-center items-center">
        <LoaderComponent />
      </div>
      <div v-else>
        <template v-if="requests.length > 0">
          <div v-for="request in requests" :key="request.id"
            class="mb-6 p-6 border border-gray-light bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
            <!-- Header Section -->
            <div class="flex justify-between items-start mb-4">
              <div class="flex items-center space-x-4">
                <template v-if="request.creatorPhoto">
                  <img :src="request.creatorPhoto" alt="User Photo" class="w-10 h-10 rounded-full" />
                </template>
                <template v-else>
                  <div
                    class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-medium text-white text-sm font-semibold">
                    {{ getUserInitials(request.demandeur) }}
                  </div>
                </template>
                <div>
                  <h3 class="text-lg font-semibold">{{ request.title }}</h3>
                  <p class="text-xs text-gray-medium">{{ formatDateString(request.dateRequest) }} - {{
                    request.demandeur }}</p>
                </div>
              </div>
              <div v-if="request.status === 'Soumis'" class="flex space-x-2">
                <button @click="handleTakeCharge(request)"
                  class="bg-yellow hover:bg-oxford-blue text-white py-2 px-4 rounded-md transition-colors duration-200">
                  Prendre en charge
                </button>
              </div>

              <div v-else class="flex space-x-2">
                <button @click="showApproveModal(request)"
                  class="bg-green hover:bg-green-dark text-white py-2 px-4 rounded-md transition-colors duration-200">
                  Accepter
                </button>
                <button @click="showRejectModal(request)"
                  class="bg-red hover:bg-red-dark text-white py-2 px-4 rounded-md transition-colors duration-200">
                  Refuser
                </button>
              </div>
            </div>

            <!-- Description Section -->
            <p class="text-sm text-gray-medium mb-4">{{ request.description }}</p>

            <!-- Documents Section -->
            <div v-if="request.documentUrls.length > 0" class="mt-4">
              <h4 class="flex items-center text-md font-semibold mb-2">
                <PaperclipIcon class="w-5 h-5 mr-2 text-gray-medium" />
                Pièces jointes
              </h4>
              <div class="flex flex-wrap gap-4">
                <div v-for="document in request.documentUrls" :key="document.url" class="w-full sm:w-1/2 lg:w-1/3 py-2">
                  <div
                    class="bg-white border border-gray-light rounded-lg p-4 flex items-center shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full mr-3">
                      <DocumentIcon :fileName="document.filename" class="text-xl" />
                    </div>
                    <a :href="document.url" target="_blank"
                      class="flex-grow text-gray-medium truncate group hover:text-oxford-blue">
                      <span class="truncate">{{ document.filename }}</span>
                    </a>
                    <DownloadIcon
                      class="w-5 h-5 text-gray-medium transition-colors duration-200 cursor-pointer ml-auto hover:text-oxford-blue"
                      @click.prevent="downloadFile(document.url)" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>

        <template v-else>
          <p>Pas de demandes à valider</p>
        </template>
      </div>
    </div>

    <!-- Modals pour validation des demandes -->
    <ModalVue :isOpen="openApproveModal" title="Accepter la demande" @close="handleApproveModalClose">
      <div>
        <textarea v-model="approveMessage" placeholder="Message d'approbation (optionnel)"
          class="w-full p-2 my-2 border border-gray-light text-sm rounded-md"></textarea>
        <div class="file-upload-container">
          <div
            class="upload-area py-16 border-2 border-dashed bg-gray-light gray-light-medium cursor-pointer hover:border-blue hover:text-blue"
            @dragover.prevent @drop="handleDrop" @click="clickFileInput">
            <p class="text-center">Déposez vos fichiers ici ou cliquez pour les téléverser.</p>
            <input id="fileInput" type="file" ref="fileInput" @change="handleFileChange" class="hidden" multiple />
          </div>
          <div v-if="uploadedFiles.length > 0" class="mt-4">
            <UploadFileList v-for="file in uploadedFiles" :key="file.name" :file-name="file.name"
              :remove-file="removeFile" />
          </div>
        </div>
        <LoaderComponent v-if="processing" />
        <button @click="approveRequest" :disabled="processing" class="bg-green text-white py-2 px-4 mt-2 rounded-md">
          Confirmer l'approbation
        </button>
      </div>
    </ModalVue>
    <ModalVue :isOpen="openRejectModal" title="Refuser la demande" @close="handleRejectModalClose">
      <div>
        <textarea v-model="rejectMessage" placeholder="Raison du refus (optionnel)"
          class="w-full p-2 border border-gray-light rounded-md"></textarea>
        <LoaderComponent v-if="processing" />
        <button @click="rejectRequest" :disabled="processing" class="bg-red text-white py-2 px-4 mt-2 rounded-md">
          Confirmer le refus
        </button>
      </div>
    </ModalVue>
    <ModalVue :isOpen="openEditModal" title="Modifier la demande" @close="handleEditModalClose">
      <EditRequestForm :demande="currentRequest" @save="saveEditRequest" />
    </ModalVue>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import CardList from '@/components/CardList.vue'
import ModalVue from '@/components/ModalVue.vue'
import CreateRequest from '@/components/CreateRequest.vue'
import RequestDetails from '@/components/RequestDetails.vue'
import LoaderComponent from '@/components/LoaderComponent.vue'
import UploadFileList from '@/components/UploadFileList.vue'
import { useDemandStore } from '@/stores/UserStore/DemandStore'
import { useDocumentStore } from '@/stores/UserStore/DocumentsStore'
import { useUserStore } from '@/stores/UserStore/UserStore'
import EditRequestForm from '@/components/Forms/EditRequestForm.vue';
import { formatDateString, getUserInitials } from '@/utils'

// États et stores pour les sections
const open = ref(false)
const openRequestModal = ref(false)
const searchQuery = ref('')
const clickRequest = ref({})
const selectedFilter = ref('all')


// Initialiser currentSection selon le rôle de l'utilisateur
const userStore = useUserStore()
const currentSection = ref(userStore.user.role === 'Administrateur' ? 'validation' : 'requests')

const demandsStore = useDemandStore()
const documentStore = useDocumentStore()

// États pour Validation des demandes
const requests = ref([])
const loading = ref(true)
const openApproveModal = ref(false)
const openRejectModal = ref(false)
const currentRequest = ref(null)
const approveMessage = ref('')
const rejectMessage = ref('')
const uploadedFiles = ref([])
const processing = ref(false)
const openEditModal = ref(false);
const handleModalClose = () => {
  open.value = false
}

const handleRequestModalClose = () => {
  openRequestModal.value = false
}

const viewRequest = (demande) => {
  openRequestModal.value = true
  clickRequest.value = demande
}

const filteredRequests = computed(() => {
  let filtered = demandsStore.demandes

  if (selectedFilter.value !== 'all') {
    filtered = filtered.filter((demande) => {
      if (selectedFilter.value === 'Pending') return demande.status === 'Soumis'
      if (selectedFilter.value === 'inProgress') return demande.status === 'En cours'
      if (selectedFilter.value === 'Approuvée') return demande.status === 'Approuvée'
      if (selectedFilter.value === 'Refusée') return demande.status === 'Refusée'
    })
  }

  if (searchQuery.value) {
    filtered = filtered.filter((demande) =>
      demande.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  return filtered
})

const token = localStorage.getItem('jwt_token')
const documents = computed(() => documentStore.getDocuments())

const recentDocuments = computed(() => {
  return documents.value.slice(0, 5) // Affiche les 5 documents les plus récents
})

const loadRequestsToValidate = async () => {
  if (demandsStore.demandesLoaded === true) {
    console.log(1)
    requests.value = demandsStore.demandesToBeValidated
    loading.value = false
  }
  else {
    console.log(2)
    loading.value = true
    await demandsStore.loadDemandesToBeValidated()
    requests.value = demandsStore.demandesToBeValidated
    loading.value = false
  }

}

const approveRequest = async () => {
  loading.value = true
  processing.value = true
  try {
    await demandsStore.approveRequest(currentRequest.value.id, approveMessage.value, uploadedFiles.value)
    handleApproveModalClose()
  } finally {
    processing.value = false
    await loadRequestsToValidate()
    loading.value = false
  }
}

const rejectRequest = async () => {
  loading.value = true
  processing.value = true
  try {
    await demandsStore.rejectRequest(currentRequest.value.id, rejectMessage.value)
    handleRejectModalClose()
  } finally {
    processing.value = false
    await loadRequestsToValidate()
    loading.value = false
  }
}

const handleTakeCharge = async (request) => {
  console.log(request.id)
  loading.value = true;
  try {
    await demandsStore.takeChargeRequest(request.id);
    await loadRequestsToValidate();
  } catch (error) {
    console.error('Erreur lors de la prise en charge de la demande :', error);
  } finally {
    loading.value = false;
  }
};

const showApproveModal = (request) => {
  currentRequest.value = request
  openApproveModal.value = true
}

const showRejectModal = (request) => {
  currentRequest.value = request
  openRejectModal.value = true
}

const handleApproveModalClose = () => {
  openApproveModal.value = false
  approveMessage.value = ''
  uploadedFiles.value = []
  currentRequest.value = null
}

const handleRejectModalClose = () => {
  openRejectModal.value = false
  rejectMessage.value = ''
  currentRequest.value = null
}

const showEditModal = (demande) => {
  currentRequest.value = demande;
  openEditModal.value = true;
};

const handleEditModalClose = () => {
  openEditModal.value = false;
  currentRequest.value = null;
};

const saveEditRequest = async (updatedRequest) => {
  try {
    await demandsStore.updateRequest(updatedRequest);
    await demandsStore.loadDemandes();
    handleEditModalClose();
  } catch (error) {
    console.error('Erreur lors de la mise à jour de la demande :', error);
  }
};


const handleDrop = async (event) => {
  event.preventDefault()
  try {
    uploadedFiles.value = event.dataTransfer.files
  } catch (error) {
    console.error('Erreur lors du traitement des fichiers :', error)
  }
}

const clickFileInput = () => {
  const input = document.getElementById('fileInput')
  if (input) {
    input.click()
  }
}

const handleFileChange = (e) => {
  uploadedFiles.value = Array.from(e.target.files)
}

const removeFile = (fileName) => {
  uploadedFiles.value = uploadedFiles.value.filter((file) => file.name !== fileName)
}

const downloadFile = (url) => {
  const link = document.createElement('a')
  link.href = url
  link.download = true
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

// Chargement des données
onMounted(async () => {
  await demandsStore.fetchMotifs();
  await demandsStore.loadDemandes()
  demandsStore.demandesLoaded = false
  if (userStore.user.role !== "Salarie") {
    await loadRequestsToValidate()
  }
  if (!documentStore.areDocumentsLoaded()) {
    await documentStore.fetchDocuments(token)
  }
})
</script>
