<template>
  <main class="max-w-7xl mx-auto mt-4">
    <!-- <ProgressBar></ProgressBar> -->
    <div class="flex justify-between items-center my-8">
      <h3 class="text-2xl font-bold">Mes documents</h3>
      <el-button type="primary" class="ml-auto" size="large" @click="toggleUploadArea">
        Nouveau document
        <el-icon class="el-icon--right">
          <Upload />
        </el-icon>
      </el-button>
    </div>

    <DocumentUploader v-if="showUploadDocument" @documentUploaded="documentUploaded" />
    <TableRecentDocuments :reloadDocuments="reloadDocuments" @row-clicked="openFileDetails" />

    <div class="flex justify-between items-center my-8">
      <h3 class="text-2xl font-bold">Mes dossiers récents</h3>
      <el-button type="primary" class="ml-auto" size="large" @click="toggleFolderCreation">
        Nouveau dossier
        <el-icon class="el-icon--right">
          <FolderAdd />
        </el-icon>
      </el-button>
    </div>

    <FolderUploader v-if="showFolderCreation" @folderCreated="onFolderCreated" />
    <RecentFolders :reload-key="reloadKey"></RecentFolders>

    <transition name="slide">
      <aside v-if="showFileDetails"
        class="fixed right-0 top-0 bottom-0 w-80 bg-white shadow-lg border-l border-gray-light overflow-y-auto transition-transform transform"
        style="z-index: 9999;">
        <header class="flex justify-between items-center px-4 py-3 border-b border-gray-light bg-gray-50">
          <h3 class="text-lg font-semibold">Détails du fichier</h3>
          <el-button icon="Close" type="text" @click="closeFileDetails"></el-button>
        </header>

        <div class="p-4 flex flex-col items-center">
          <!-- Icône dans une case transparente -->
          <div class="bg-white w-full h-36 p-4 border rounded-sm border-gray-300 mb-4 flex items-center justify-center">
            <DocumentIcon :fileName="selectedFile.name" class="w-2" />
          </div>

          <!-- Nom du fichier et taille en bas avec un bouton pour télécharger -->
          <div class="w-full flex justify-between items-center mb-6">
            <div class="text-left">
              <p class="text-lg font-normal">{{ selectedFile.name }}</p>
              <p class="text-sm text-gray-500">Taille : {{ formatSize(selectedFile.size) }}</p>
            </div>
            <el-button type="primary" icon="Download" size="small" round @click="downloadFile" class="ml-4">
              Télécharger
            </el-button>
          </div>

          <!-- Informations supplémentaires -->
          <div class="bg-gray-50 p-3 rounded-md">
            <p class="text-sm text-gray-600 mb-2">
              <strong class="text-gray-800">Date de création :</strong> {{ formatDate(selectedFile.created_at) }}
            </p>
            <p class="text-sm text-gray-600 mb-2">
              <strong class="text-gray-800">Envoyé par :</strong> {{ selectedFile.uploadedBy }}
            </p>
            <p class="text-sm text-gray-600">
              <strong class="text-gray-800">Partagé avec :</strong>
              <br>
            </p>
            <ul class="list-disc list-inside text-sm text-gray-600">
              <li v-for="(user, index) in selectedFile.sharedWith" :key="index">{{ user }}</li>
            </ul>
          </div>
        </div>
      </aside>
    </transition>

  </main>
</template>

<script lang="ts" setup>
import { defineAsyncComponent, ref } from 'vue';
import TableRecentDocuments from '@/views/TableRecentDocuments.vue'
import RecentFolders from '@/views/RecentFolders.vue'
import DocumentIcon from '@/components/DocumentIcon.vue';
const DocumentUploader = defineAsyncComponent(() => import('@/components/DocumentUploader.vue'));
const FolderUploader = defineAsyncComponent(() => import('@/components/FolderUploader.vue'));

const showUploadDocument = ref(false)
const showFolderCreation = ref(false)
const reloadKey = ref(0);
const reloadDocuments = ref(0);

const showFileDetails = ref(false);
const selectedFile = ref({
  name: '',
  size: 0,
  created_at: '',
  uploadedBy: '',
  sharedWith: [],
  url: '',
  id: null,
});

const openFileDetails = (file) => {
  selectedFile.value = {
    name: file.name,
    size: file.size,
    created_at: file.created_at,
    uploadedBy: `${file['first_name (from uploaded_by)']} ${file['last_name (from uploaded_by)']}`,
    sharedWith: file['first_name (from user_receiver)']?.map((_, index) => {
      return `${file['first_name (from user_receiver)'][index]} ${file['last_name (from user_receiver)'][index]}`;
    }) || [],
    url: file.url,
    id: file.id,
  };
  showFileDetails.value = true;
};

const closeFileDetails = () => {
  showFileDetails.value = false;
  selectedFile.value = {};
};

function downloadFile() {
  if (!selectedFile.value.url) {
    alert("Aucun fichier disponible pour l'ouverture.");
    return;
  }

  const link = document.createElement("a");
  link.href = selectedFile.value.url;
  link.target = "_blank"; // Ouvre le fichier dans un nouvel onglet
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link); // Nettoyage
}


function formatSize(sizeInKo) {
  if (sizeInKo >= 1024) {
    const sizeInMo = sizeInKo / 1024;
    return `${sizeInMo.toFixed(2)} Mo`;
  } else {
    return `${sizeInKo.toFixed(2)} Ko`;
  }
}

function formatDate(date) {
  const options = {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  };
  return new Date(date).toLocaleDateString('fr-FR', options).replace(',', ' à');
}

const toggleUploadArea = () => {
  showUploadDocument.value = !showUploadDocument.value
}

const toggleFolderCreation = () => {
  showFolderCreation.value = !showFolderCreation.value
}

const onFolderCreated = () => {
  showFolderCreation.value = false
  reloadKey.value += 1;
};

const documentUploaded = () => {
  showUploadDocument.value = false
  reloadDocuments.value += 1;
};

</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}

.slide-enter-from {
  transform: translateX(100%);
}

.slide-leave-to {
  transform: translateX(100%);
}

aside {
  z-index: 9999;
}
</style>
