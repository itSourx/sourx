<template>
  <div class="container mx-auto" v-if="folder">
    <div class="flex justify-between border-b border-gray-light mb-4">
      <h1 class="text-xl font-bold">{{ folder.title }}</h1>
      <button @click="openRenameModal"
        class="flex items-center bg-blue hover:bg-oxford-blue text-white font-bold py-2 my-1 px-4 rounded-md shadow-md">
        <PlusCircleIcon class="mr-2 w-4 h-4" />
        Renommer le dossier
      </button>
    </div>

    <!-- Liste des documents du dossier -->
    <div class="overflow-x-auto mb-4">
      <table class="min-w-full divide-y divide-gray-medium">
        <thead class="text-gray-medium">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
              Fichier
            </th>
            <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-medium">
          <tr v-if="folder.documents.length === 0">
            <td colspan="2" class="px-6 py-4 text-center text-gray-500">
              Aucun document disponible dans ce dossier.
            </td>
          </tr>
          <tr v-for="document in folder.documents" :key="document.id"
            class="hover:bg-gray-light transition-colors duration-200">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <DocumentIcon :fileName="document.title" class="text-xl mr-4" />
                <div class="text-sm font-medium text-gray-900">{{ document.title }}</div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
              <div class="flex justify-center space-x-4">
                <a :href="document.url" target="_blank" class="transition-colors duration-300">
                  <EyeIcon class="w-4 h-4 text-gray-medium" />
                </a>
                <a :href="document.url" download class="transition-colors duration-300">
                  <DownloadIcon class="w-4 h-4 text-gray-medium" />
                </a>
                <button @click="deleteDocument(document.id)" class="transition-colors duration-300">
                  <Trash class="w-4 h-4 text-red hover:text-gray-medium" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal pour renommer le dossier -->
    <ModalVue :isOpen="renameModalOpen" title="Renommer le dossier" @close="closeRenameModal">
      <div class="p-4">
        <label class="block text-gray-700 text-sm font-bold mb-2">Nouveau nom</label>
        <input v-model="newFolderName" type="text"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
        <button @click="renameFolder"
          class="mt-4 bg-blue hover:bg-oxford-blue text-white font-bold py-2 px-4 rounded-md shadow-md">
          Renommer
        </button>
      </div>
    </ModalVue>

    <!-- Liste des dossiers en bas -->
    <div class="mt-8">
      <h2 class="text-lg font-bold mb-4">Autres dossiers</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <div v-for="otherFolder in folders" :key="otherFolder.id"
          class="bg-white p-6 rounded-lg flex flex-col items-center cursor-pointer" @click="loadFolder(otherFolder.id)">
          <img :src="otherFolder.logo" alt="folder icon" class="w-12 h-12 mb-2" />
          <div class="text-sm text-gray-medium">{{ otherFolder.title }}</div>
          <div class="text-xs text-gray-medium">{{ otherFolder.documents.length }} document(s)</div>
        </div>
      </div>
    </div>
  </div>
  <div v-else class="text-center text-gray-medium py-8">
    <p>Chargement du dossier...</p>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useFolderStore } from '@/stores/UserStore/FolderStore'
import { useDocumentStore } from '@/stores/UserStore/DocumentsStore'
import ModalVue from '@/components/ModalVue.vue'

const folderStore = useFolderStore()
const documentStore = useDocumentStore()
const route = useRoute()
const folder = ref(null)
const renameModalOpen = ref(false)
const newFolderName = ref('')

const loadFolder = async (folderId = route.params.id) => {
  const fetchedFolder = folderStore.getFolderById(folderId)
  if (fetchedFolder) {
    folder.value = fetchedFolder
  }
}

const openRenameModal = () => {
  renameModalOpen.value = true
}

const closeRenameModal = () => {
  renameModalOpen.value = false
}

const renameFolder = async () => {
  if (newFolderName.value.trim()) {
    await folderStore.renameFolder(folder.value.id, newFolderName.value.trim())
    closeRenameModal()
    loadFolder(folder.value.id)
  }
}

const deleteDocument = async (documentId) => {
  const confirmed = confirm('Êtes-vous sûr de vouloir supprimer ce document ?')
  if (confirmed) {
    try {
      await documentStore.deleteDocument(documentId)
      loadFolder(folder.value.id)
    } catch (error) {
      console.error('Erreur lors de la suppression du document:', error)
    }
  }
}

const folders = computed(() => folderStore.folders)

onMounted(() => {
  loadFolder()
})
</script>
