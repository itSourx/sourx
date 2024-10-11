<template>
  <div class="file-upload-container">
    <div
      class="upload-area py-16 border-2 border-dashed bg-gray-light gray-light-medium cursor-pointer hover:border-blue hover:text-blue"
      @dragover.prevent @drop="handleDrop" @click="clickFileInput">
      <p class="text-center">Déposez vos fichiers ici ou cliquez pour les téléverser.</p>
      <input id="fileInput" type="file" ref="fileInput" @change="handleFileChange" class="hidden" multiple />
    </div>

    <div v-if="uploadedData.length > 0" class="mt-4">
      <UploadFileList v-for="file in uploadedData" :key="file.name" :file-name="file.name" :remove-file="removeFile" />


      <div class="mt-4" v-if="user.role !== 'Salarie' && uploadSource === 'fromDots'">
        <label for="receiver">Envoyer à:</label>
        <select id="receiver" v-model="selectedReceiver" @change="resetOptions"
          class="block w-full p-3 border bg-white border-gray-light rounded-md focus:outline-none">
          <option value="self">À moi-même</option>
        </select>
      </div>

      <div class="mt-4" v-if="user.role !== 'Salarie' && uploadSource === 'newDocuments'">
        <label for="receiver">Envoyer à:</label>
        <select id="receiver" v-model="selectedReceiver" @change="resetOptions"
          class="block w-full p-3 border bg-white border-gray-light rounded-md focus:outline-none">
          <option v-if="user.role === 'Manager'" value="individual">À mon équipe</option>
        </select>
      </div>

      <div class="mt-4" v-if="selectedReceiver === 'individual'">
        <label for="individualRecipients">Sélectionner une ou plusieurs personnes:</label>
        <MultiSelectDropdown :items="employeesUnderUser" v-model="selectedIndividuals" />
      </div>

      <div class="mt-4" v-if="selectedReceiver === 'team'">
        <label for="teamRecipients">Sélectionner une ou plusieurs équipes:</label>
        <MultiSelectDropdown :items="teamsWithEmployees" v-model="selectedTeams" label-key="team" />
      </div>

      <div class="mt-4" v-if="selectedReceiver === 'self' && uploadSource !== 'fromDots'">
        <label for="destinationFolder">Sélectionner un dossier de destination:</label>
        <select id="destinationFolder" v-model="localSelectedFolder"
          class="block w-full p-3 border bg-white border-gray-light rounded-md focus:outline-none">
          <option v-for="folder in folders" :key="folder.id" :value="folder.name">
            {{ folder.name }}
          </option>
        </select>
      </div>

      <LoaderComponent v-if="isLoading"></LoaderComponent>

      <button @click="sendDocument"
        class="bg-oxford-blue hover:bg-zaffre text-white transition-colors duration-200 font-bold py-3 my-3 px-4 rounded w-full">
        Envoyer
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, inject, watch, onMounted } from 'vue'
import UploadFileList from '@/components/UploadFileList.vue'
import MultiSelectDropdown from '@/components/MultiSelectDropdown.vue'
import LoaderComponent from '@/components/LoaderComponent.vue'
import { useUserStore } from '@/stores/UserStore/UserStore'
import { useFolderStore } from '@/stores/UserStore/FolderStore'
import { useDocumentStore } from '@/stores/UserStore/DocumentsStore'


const props = defineProps({
  forDashboard: Boolean,
  selectedFolder: String,
  uploadSource: String
})

const userStore = useUserStore()
const folderStore = useFolderStore()
const documentStore = useDocumentStore()

const isLoading = ref(false)
const uploadedData = ref([])
const selectedReceiver = ref('self')
const selectedTeams = ref([])
const selectedIndividuals = ref([])
const localSelectedFolder = ref(props.selectedFolder)

const user = userStore.user

const allEmployees = ref(userStore.employees)
const employeesUnderUser = ref([])
const teamsWithEmployees = ref([])
const folders = ref([])

const emit = defineEmits(['upload-complete'])

const getAllEmployees = () => {
  if (user.role === 'Manager' && user.Equipe != null) {

    const userEquipe = Array.isArray(user.Equipe) ? user.Equipe : Object.values(user.Equipe);
    console.log(userEquipe)

    employeesUnderUser.value = allEmployees.value.filter(
      (employee) => employee.Equipe.some(equipe => userEquipe.includes(equipe)) && employee.role === 'Salarie'
    )

    console.log(employeesUnderUser.value)
  }
}

watch(
  () => userStore.getEmployees(),
  (newVal) => {
    allEmployees.value = newVal
    getAllEmployees()
  },
  { immediate: true }
)

watch(
  employeesUnderUser,
  (newVal) => {
    console.log('employeesUnderUser updated:', newVal)
  },
  { immediate: true }
)

watch(
  () => props.selectedFolder,
  (newVal) => {
    if (newVal) {
      selectedReceiver.value = 'self'
      localSelectedFolder.value = newVal
    }
  },
  { immediate: true }
)

onMounted(async () => {
  await folderStore.fetchFolderNames()
  if (userStore.user.role !== "Salarie") {
    await userStore.fetchAllEmployees()
  }
  folders.value = documentStore.allFolders.filter(folder => folder.creatorRole === userStore.user.role)

  if (props.uploadSource === 'fromDots') {
    selectedReceiver.value = 'self' 
    localSelectedFolder.value = props.selectedFolder
  } else if (props.uploadSource === 'newDocuments') {
    selectedReceiver.value = user.role === 'Manager' ? 'team' : 'individual';
  }

  if (user.role === 'Manager' && props.uploadSource === 'newDocuments') {
    selectedReceiver.value = 'individual'; 
  }
})


// Fonction pour réinitialiser les options
const resetOptions = () => {
  if (selectedReceiver.value !== 'team') {
    selectedTeams.value = []
  }
  if (selectedReceiver.value !== 'individual') {
    selectedIndividuals.value = []
  }
  if (selectedReceiver.value !== 'self') {
    localSelectedFolder.value = ''
  }
}

const removeFile = (fileName) => {
  uploadedData.value = Array.from(uploadedData.value).filter((file) => file.name !== fileName)
}

const handleDrop = async (event) => {
  event.preventDefault()
  try {
    uploadedData.value = event.dataTransfer.files
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

const closeModal = inject('closeModal')


const handleFileChange = (e) => {
  uploadedData.value = e.target.files
}

const resetFields = () => {
  uploadedData.value = []
}

const sendDocument = async () => {
  isLoading.value = true

  let selectedUsers = []

  if (selectedReceiver.value === 'self') {
    selectedUsers.push(userStore.user.system_id)
  } else if (selectedReceiver.value === 'team') {
    selectedTeams.value.forEach((team) => {
      const teamMembers = team.members.map((member) => member.id)
      selectedUsers = [...new Set([...selectedUsers, ...teamMembers])]
    })
  } else {
    selectedUsers = selectedIndividuals.value.map((individual) => individual.id)
  }

  const filesToUpload = Array.from(uploadedData.value)

  console.log("----------7-----7---------")
  console.log(user)
  console.log(props.forDashboard ? localSelectedFolder.value : props.selectedFolder)
  console.log(selectedUsers)
  try {
    await documentStore.sendDocument(
      user,
      props.forDashboard ? localSelectedFolder.value : props.selectedFolder,
      selectedUsers,
      filesToUpload
    )

    emit('upload-complete')
  } catch (error) {
    console.error("Erreur lors de l'envoi des documents:", error)
  } finally {
    resetFields()
    isLoading.value = false
    closeModal()
  }
}
</script>

<style scoped>
.file-upload-container {
  padding: 40px 0px;
}

.upload-area {
  transition: border 0.2s ease-in-out;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 1rem;
  color: #4a5568;
}
</style>
