<template>
    <div class="file-upload-container" v-if="user.isAdmin === 1">
        <div class="upload-area py-16 border-2 border-dashed bg-gray-light gray-light-medium cursor-pointer hover:border-blue hover:text-blue"
            @dragover.prevent @drop="handleDrop" @click="clickFileInput">
            <p class="text-center">Déposez vos fichiers ici ou cliquez pour les téléverser.</p>
            <input id="fileInput" type="file" ref="fileInput" @change="handleFileChange" class="hidden" multiple />
        </div>

        <div v-if="uploadedData.length > 0" class="mt-4">
            <UploadFileList v-for="file in uploadedData" :key="file.name" :file-name="file.name"
                :remove-file="removeFile" />
            <div class="mt-4">
                <label for="receiver">Envoyer à:</label>
                <select id="receiver" v-model="selectedReceiver" @change="resetOptions"
                    class="block w-full p-3 border bg-white border-gray-light rounded-md focus:outline-none">
                    <option value="individual">À une personne</option>
                    <option value="team">À une équipe</option>
                </select>
            </div>

            <!-- Dropdown pour sélectionner des individus -->
            <div class="mt-4" v-if="selectedReceiver === 'individual'">
                <label>Sélectionner une ou plusieurs personnes:</label>
                <MultiSelectDropdown :items="formattedUsers" v-model="selectedIndividuals" />
            </div>

            <!-- Dropdown pour sélectionner une équipe -->
            <div class="mt-4" v-if="selectedReceiver === 'team'">
                <label>Sélectionner une équipe:</label>
                <MultiSelectDropdown :items="formattedTeams" v-model="selectedTeams" />
            </div>

            <LoaderComponent v-if="isLoading"></LoaderComponent>

            <button @click="sendDocument" :disabled="selectedReceiver === 'individual' && selectedIndividuals.length === 0 ||
                selectedReceiver === 'team' && selectedTeams.length === 0"
                class="bg-oxford-blue hover:bg-zaffre text-white transition-colors duration-200 font-bold py-3 my-3 px-4 rounded w-full">
                Envoyer
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, toRaw } from 'vue'
import { useUserManagementStore } from '@/stores/AdminStore/UserManagementStore'
import { useAdminDocumentStore } from '@/stores/AdminStore/DocumentsManagementStore';
import UploadFileList from '@/components/UploadFileList.vue'
import MultiSelectDropdown from '@/components/MultiSelectDropdown.vue'
import LoaderComponent from '@/components/LoaderComponent.vue'
import axios from 'axios'
import { toast } from 'vue3-toastify'

const user = JSON.parse(localStorage.getItem('user_data'))


const userManagementStore = useUserManagementStore()
const documentManagementStore = useAdminDocumentStore();

onMounted(async () => {
    await userManagementStore.initialize()
})

const isLoading = ref(false)

const uploadedData = ref([])
const selectedReceiver = ref('individual')
const selectedTeams = ref([])
const selectedIndividuals = ref([])

// Formatage des utilisateurs pour le dropdown
const formattedUsers = computed(() => {
    return userManagementStore.users
        .filter(e => e.email !== user.email)
        .map((user) => ({
            id: user.id,
            name: `${user.prenom} ${user.nom}`,
            photo: user.photo
        }))
})

// Formatage des équipes pour le dropdown
const formattedTeams = computed(() => {
    return userManagementStore.teams.map((team) => ({
        id: team.name,
        name: team.name,
        members: team.members
    }))
})

// Fonction pour réinitialiser les sélections
const resetOptions = () => {
    if (selectedReceiver.value !== 'team') {
        selectedTeams.value = []
    }
    if (selectedReceiver.value !== 'individual') {
        selectedIndividuals.value = []
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

const emit = defineEmits(['upload-complete'])

const handleUploadComplete = () => {
    resetFields()
    emit('upload-complete')
}

const handleFileChange = (e) => {
    uploadedData.value = e.target.files
}

const resetFields = () => {
    uploadedData.value = []
}

// Fonction pour envoyer le document
const sendDocument = async () => {
    isLoading.value = true

    const token = localStorage.getItem('jwt_token')
    const selectedUsers = []

    // Ajouter les utilisateurs sélectionnés (si "individual" est choisi)
    if (selectedReceiver.value === 'individual') {
        selectedUsers.push(...selectedIndividuals.value.map((individual) => individual.id))
    }

    // Ajouter les membres de l'équipe sélectionnée (si "team" est choisi)
    if (selectedReceiver.value === 'team') {

        const selectedTeam = selectedTeams.value[0] // Supposons qu'une seule équipe soit sélectionnée à la fois

            /*      
                console.log("éééé------------------------------------")
                console.log(selectedTeam)
                console.log(selectedTeams.value.map(item => toRaw(item)))
                console.log("-éééé-----------------------------------")
        
                (selectedTeams.value.map(item => toRaw(item))).forEach(oneTeam => {
                    const team = userManagementStore.teams.find((team) => team.name === oneTeam.id)
                    if (team) {
                        selectedUsers.push(...team.members.map((member) => member.id))
                    }
                });
        
                console.log(selectedUsers) */


        if (selectedTeam) {
            const team = userManagementStore.teams.find((team) => team.name === selectedTeam.id)
            if (team) {
                selectedUsers.push(...team.members.map((member) => member.id))
            }
        }

        console.log(selectedUsers)
    }

    if (selectedUsers.length === 0) {
        toast.error('Veuillez sélectionner au moins un utilisateur ou une équipe avant de procéder.')
        isLoading.value = false
        return
    }

    const filesToUpload = Array.from(uploadedData.value)

    let formData = new FormData()
    formData.append('selectedUsers', JSON.stringify(selectedUsers))
    filesToUpload.forEach((file, index) => {
        formData.append(`filesToUpload[${index}]`, file)
    })

    try {

        console.log(formData)
        const response = await axios.post(
            'https://sourxhrtest-a90509d4033e.herokuapp.com/api/v1/sendDocumentByAdmin',
            formData,
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'multipart/form-data'
                }
            }
        )

        if (response.status === 200) {
            toast.success(response.data.message)
            await documentManagementStore.refreshDocuments();
        } else {
            toast.error(response.data.message)
        }
    } catch (error) {
        console.error("Erreur lors de l'envoi des documents:", error)
        toast.error("Erreur lors de l'envoi des documents")
    } finally {
        handleUploadComplete()
        isLoading.value = false
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