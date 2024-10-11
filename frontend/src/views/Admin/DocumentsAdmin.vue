<template>
  <div class="max-w-7xl mx-auto p-6">
    <LoaderComponent v-if="loading" />
    <AdminFolderManager v-else :files="files" :folders="folders" @create-folder="handleCreateFolder"
      @rename-item="handleRenameItem" @remove-folder="handleRemoveFolder" @remove-file="handleRemoveFile"
      @upload-document="handleUploadDocument" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAdminDocumentStore } from '@/stores/AdminStore/DocumentsManagementStore'
import AdminFolderManager from '@/views/Admin/FolderManagerAdmin.vue'
import LoaderComponent from '@/components/LoaderComponent.vue'

const store = useAdminDocumentStore()

// Variables réactives pour les dossiers, fichiers et état de chargement
const folders = ref([])
const files = ref([])
const loading = ref(true)

onMounted(async () => {
  loading.value = true;
  //store.documentsLoaded = false
  try {
    const token = localStorage.getItem('jwt_token');
    await store.fetchFoldersAndFiles(token);

    folders.value = store.folders;
    files.value = store.folders.flatMap(folder => folder.documents);
  } catch (error) {
    console.error("Erreur lors du chargement des fichiers et dossiers :", error);
  } finally {
    loading.value = false;
  }

})

// Gestion de la création d'un nouveau dossier
const handleCreateFolder = async (name) => {
  console.log("handleCreateFolder appelé avec:", name);
  await store.createFolder(name.name);
};

// Gestion du renommage d'un dossier ou d'un fichier
const handleRenameItem = async ({ item, newName, type }) => {
  if (type === 'folder') {
    store.renameFolder(item.id, newName)
  } else if (type === 'file') {
    await store.renameItem(item.id, newName, 'file')
  }
}

// Gestion de la suppression d'un dossier
const handleRemoveFolder = async (folder) => {
  await store.removeFolder(folder.id)
}

// Gestion de la suppression d'un fichier
const handleRemoveFile = async (file) => {
  const parentFolder = store.folders.find(folder => folder.documents.some(doc => doc.id === file.id))
  if (parentFolder) {
    await store.removeFile(file.id)
  }
}

// Gestion de l'upload d'un document
const handleUploadDocument = async (document) => {
  loading.value = true;
  store.documentsLoaded = false
  try {
    await store.uploadDocument(document);
    await store.fetchFoldersAndFiles(localStorage.getItem('jwt_token'));

    folders.value = store.folders;
    files.value = store.folders.flatMap(folder => folder.documents);

  } catch (error) {
    console.error("Erreur lors de l'upload du document :", error);
  } finally {
    loading.value = false;
  }
};

</script>
