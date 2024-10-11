<template>
  <div class="max-w-7xl mx-auto">

    <div class="px-4">
      <div class="flex justify-between">
        <h1 class="text-2xl my-4">Mes documents</h1>
        <div class="flex gap-2">
          <button @click="openCreateFolderModal"
            class="flex items-center bg-oxford-blue hover:bg-zaffre text-white font-bold px-4 py-2 rounded-md shadow-md">
            <PlusCircleIcon class="mr-2" />
            Nouveau Dossier
          </button>
          <button @click="openUploadModal('newDocuments', '')" v-if="userStore.user.role !== 'Salarie'"
            class="flex items-center bg-oxford-blue hover:bg-zaffre text-white font-bold px-4 py-2 rounded-md shadow-md">
            <PlusCircleIcon class="mr-2" />
            Nouveau Document(s)
          </button>
        </div>

      </div>
    </div>

    <template v-if="documentsLoaded">
      <FileManager :rootFolders="rootFolders" :user="userStore.user" @remove-folder="handleRemoveFolder"
        @rename-folder="handleRenameFolder" @remove-file="handleRemoveFile" @move-file="handleMoveFile"
        @add-document="openUploadModal('fromDots', $event)">
      </FileManager>

    </template>
    <template v-else>
      <LoaderComponent></LoaderComponent>
    </template>

    <ModalVue :isOpen="open" title="Ajouter un document" @close="handleModalClose">
      <UploadArea :selectedFolder="selectedFolder" :forDashboard="false" :uploadSource="uploadSource"
        @upload-complete="handleUploadFile"></UploadArea>
    </ModalVue>

    <ModalVue :isOpen="isCreateFolderModalOpen" title="Nouveau Dossier" @close="handleModalFolderClose">
      <div class="p-4">
        <input v-model="newFolderName" type="text" placeholder="Nom du dossier"
          class="w-full border border-gray-light rounded-md px-4 py-2 focus:outline-none" />
        <LoaderComponent v-if="isCreatingFolder" />
        <div class="flex justify-end mt-4">
          <button @click="createFolder" :disabled="isCreatingFolder"
            class="bg-oxford-blue text-white px-4 py-2 rounded-md shadow-md hover:bg-zaffre">
            Créer
          </button>
        </div>
      </div>
    </ModalVue>

    <LoaderComponent v-if="loaderFolderOrFIleCreation"></LoaderComponent>

  </div>
</template>

<script setup>
import { ref, onMounted, provide } from 'vue'

import FileManager from '@/components/FileManager.vue'
import ModalVue from '@/components/ModalVue.vue'
import UploadArea from '@/components/UploadArea.vue'
import { useDocumentStore } from '@/stores/UserStore/DocumentsStore'
import { useFolderStore } from '@/stores/UserStore/FolderStore'
import { useUserStore } from '@/stores/UserStore/UserStore'

const documentStore = useDocumentStore()
const userStore = useUserStore()
const folderStore = useFolderStore()



const rootFolders = ref([])
const selectedFolder = ref('')
const documentsLoaded = ref(false)
const uploadSource = ref('')
const open = ref(false)
const newFolderName = ref('')
const isCreateFolderModalOpen = ref(false)
const isCreatingFolder = ref(false)
const loaderFolderOrFIleCreation = ref(false)



const handleModalFolderClose = async () => {
  isCreateFolderModalOpen.value = false
  localStorage.removeItem('folders');
}


const openCreateFolderModal = () => {
  newFolderName.value = ''
  isCreateFolderModalOpen.value = true
}

const openUploadModal = (source, folderName) => {
  selectedFolder.value = folderName
  uploadSource.value = source
  open.value = true
}

const openUploadModalForFolder = (folderName) => {
  selectedFolder.value = folderName
  open.value = true
}

const populateFilesAndFolders = () => {
  const foldersMap = new Map()

  // Initialiser tous les dossiers
  documentStore.allFolders.forEach((folder) => {
    foldersMap.set(folder.name, {
      id: folder.id,
      name: folder.name,
      sender: folder.creatorName === userStore.user.username ? 'Moi' : folder.creatorName,
      creatorRole: folder.creatorRole,
      documentCount: 0,
      files: [],
      size: 0
    })
  })

  // Ajouter les fichiers dans les dossiers respectifs
  documentStore.allDocuments.forEach((item) => {
    const file = {
      id: item.id,
      name: item.title,
      sender: item.creatorName === userStore.user.username ? 'Moi' : item.creatorName,
      dateAdded: new Date(item.createdTime).toLocaleDateString(),
      size: item.size || '--',
      url: item.url
    }

    if (item.folder) {

      const folderKey = typeof item.folder === 'object' && item.folder !== null ? item.folder.name : item.folder

      if (foldersMap.has(folderKey)) {
        const folder = foldersMap.get(folderKey)
        folder.files.push(file)
        folder.size += parseFloat(item.size) || 0
      } else {
        // S'assurer que le dossier est bien enregistré
        foldersMap.set(folderKey, {
          id: foldersMap.size + 1,
          name: folderKey,
          sender: item.folderCreator,
          documentCount: 1,
          files: [file],
          size: parseFloat(item.size) || 0
        })
      }
    }
  })


  rootFolders.value = Array.from(foldersMap.values())
}


const loadDocuments = async () => {
  if (documentStore.areDocumentsLoaded()) {
    populateFilesAndFolders(documentStore.getDocuments())
    documentsLoaded.value = true
  } else {
    await documentStore.fetchDocuments(localStorage.getItem('jwt_token')).then(() => {
      console.log(documentStore.allDocuments)
      populateFilesAndFolders(documentStore.getDocuments())
      documentsLoaded.value = true
    })
  }
}

// Gérer la suppression d'un dossier
const handleRenameFolder = async (folder, newFolderName) => {
  const confirmed = confirm(
    `Êtes-vous sûr de vouloir renommer le dossier "${folder.name}" en "${newFolderName}" ?`
  )

  if (confirmed) {
    documentStore.documentsLoaded = false
    loaderFolderOrFIleCreation.value = true
    try {
      await folderStore.renameFolder(folder, newFolderName)
      await loadDocuments()
    } catch (error) {
      console.error('Erreur lors du renommage du dossier:', error)
    } finally {
      loaderFolderOrFIleCreation.value = false
    }
  }
}

// Gérer la suppression d'un dossier
const handleRemoveFolder = async (folder) => {
  const confirmed = confirm(
    `Êtes-vous sûr de vouloir archiver le dossier "${folder.name}" et tout son contenu ?`
  )
  if (confirmed) {
    loaderFolderOrFIleCreation.value = true
    documentStore.documentsLoaded = false
    try {
      await folderStore.deleteFolder(folder)
      await loadDocuments()
    } catch (error) {
      console.error('Erreur lors de la suppression du dossier:', error)
    } finally {
      loaderFolderOrFIleCreation.value = false
    }
  }
}

const handleRemoveFile = async (file) => {
  const confirmed = confirm(`Êtes-vous sûr de vouloir archiver le fichier "${file.name}" ?`)
  if (confirmed) {
    loaderFolderOrFIleCreation.value = true
    documentStore.documentsLoaded = false
    try {
      await documentStore.deleteDocument(file.id)
      await loadDocuments()

    } catch (error) {
      console.error('Erreur lors de l\'archivage du fichier:', error)
    } finally {
      loaderFolderOrFIleCreation.value = false
    }
  }
}

// Gérer le déplacement d'un fichier
const handleMoveFile = async ({ file, targetFolder, currentFolder }) => {
  documentStore.documentsLoaded = false
  loaderFolderOrFIleCreation.value = true
  try {
    await documentStore.moveDocument(file.id, targetFolder.name)
    await loadDocuments()
  } catch (error) {
    console.error('Erreur lors du déplacement du fichier:', error)
  } finally {
    loaderFolderOrFIleCreation.value = false
  }

}

const createFolder = async () => {
  try {
    loaderFolderOrFIleCreation.value = true
    isCreatingFolder.value = true
    await folderStore.createFolder(newFolderName.value);
    isCreateFolderModalOpen.value = false
    documentStore.documentsLoaded = false
    await loadDocuments()
  } catch (error) {
    console.error('Erreur lors de la création du dossier:', error)
  } finally {
    isCreatingFolder.value = false
    loaderFolderOrFIleCreation.value = false
  }
};

const resetUploadFields = () => {
  selectedFolder.value = '';
  uploadSource.value = '';
};

const handleModalClose = () => {
  open.value = false
  resetUploadFields();
}

provide('closeModal', handleModalClose)

const handleUploadFile = async () => {
  handleModalClose()
  documentStore.documentsLoaded = false
  loaderFolderOrFIleCreation.value = true
  try {
    await loadDocuments()
  } catch (error) {
    console.error('Erreur lors du chargement du fichier:', error)
  } finally {
    loaderFolderOrFIleCreation.value = false
  }
}

onMounted(async () => {
  await loadDocuments()
})
</script>
