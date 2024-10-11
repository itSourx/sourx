<template>
  <div class="py-8">
    <form @submit.prevent="createDemand" class="text-gray-medium">
      <!-- <div class="mb-4">
        <label for="title" class="block text-sm font-medium">Titre * </label>
        <input v-model="formData.title" type="text" id="title" name="title"
          class="pl-3 mt-1 p-3 w-full rounded-md text-xs border border-gray-light" required />
      </div> -->

      <div class="mb-4">
        <label for="motif" class="block text-sm font-medium">Motif *</label>
        <select v-model="formData.motif" id="motif" name="motif"
          class="pl-3 mt-1 p-3 w-full rounded-md text-xs border border-gray-light" required>
          <option value="" disabled selected>--Choisir un motif--</option>
          <option v-for="motif in demandsStore.motifs" :key="motif.id" :value="motif.id">
            {{ motif.title }}
          </option>
        </select>
      </div>

      <div class="mb-4">
        <label for="description" class="block text-sm font-medium">Description</label>
        <textarea v-model="formData.description" id="description" name="description" rows="3"
          class="pl-3 mt-1 p-3 w-full rounded-md text-xs border border-gray-light"></textarea>
      </div>

      <div
        class="upload-area py-16 border-2 border-dashed bg-gray-light gray-light-medium cursor-pointer hover:border-oxford-blue hover:text-oxford-blue"
        @dragover.prevent="handleDragOver" @dragleave.prevent="handleDragLeave" @drop="handleDrop"
        @click="clickFileInput">
        <p class="text-center">Déposez vos fichiers ici ou cliquez pour les téléverser.</p>
        <input id="fileInput" type="file" ref="fileInput" @change="handleFileChange" class="hidden" multiple />
      </div>

      <div v-if="uploadedData.length > 0" class="mt-4">
        <UploadFileList v-for="file in uploadedData" :key="file.name" :file-name="file.name"
          :remove-file="removeFile" />
      </div>

      <LoaderComponent v-if="isLoading"></LoaderComponent>

      <small v-if="errorMessage" class="text-red mb-4">{{ errorMessage }}</small>
      <button type="submit" class="bg-oxford-blue hover:bg-zaffre text-white p-3 w-full rounded-md mt-5">
        Faire la demande
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useDemandStore } from '@/stores/UserStore/DemandStore';
import UploadFileList from '@/components/UploadFileList.vue'
import LoaderComponent from '@/components/LoaderComponent.vue'
import { toast } from 'vue3-toastify'

const formData = ref({
  motif: '', 
  description: ''
});

const uploadedData = ref([])
let errorMessage = ''

const emits = defineEmits(['close', 'requestCreated'])
const demandsStore = useDemandStore();
const isLoading = ref(false)
const isDragging = ref(false);

const handleDragOver = () => {
  isDragging.value = true;
};

const handleDragLeave = () => {
  isDragging.value = false;
};


const createDemand = async () => {
  isLoading.value = true;
  try {
    await demandsStore.createDemand(formData.value, uploadedData.value); 
    formData.value.motif = '';
    formData.value.description = '';
    uploadedData.value = [];
    emits('requestCreated');
    emits('close');
  } catch (error) {
    errorMessage = 'Erreur lors de la création de la demande. Veuillez réessayer.';
    console.error(error);
  } finally {
    isLoading.value = false;
  }
};

const handleFiles = (files) => {
  const fileArray = Array.from(files);
  const maxSize = 5 * 1024 * 1024;
  const acceptedTypes = [
    'application/msword', // doc
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
    'application/vnd.ms-powerpoint', // ppt
    'application/vnd.openxmlformats-officedocument.presentationml.presentation', // pptx
    'application/vnd.ms-excel', // xls
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
    'text/plain',
    'text/html',
    'application/pdf',
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/bmp',
    'image/webp'
  ];

  fileArray.forEach((file) => {
    if (file.size > maxSize) {
      alert(`Le fichier ${file.name} dépasse la taille maximale de 5 Mo.`);
      return;
    }
    if (!acceptedTypes.includes(file.type)) {
      alert(`Le fichier ${file.name} n'est pas d'un type accepté.`);
      return;
    }

    uploadedData.value.push(file);
  });
};

const handleDrop = async (event) => {
  event.preventDefault()
  errorMessage = ''
  handleFiles(event.dataTransfer.files)
  uploadedData.value = event.dataTransfer.files
}

const clickFileInput = () => {
  errorMessage = ''
  const input = document.getElementById('fileInput')
  if (input) {
    input.click()
  }
}

const handleFileChange = (e) => {
  handleFiles(e.target.files)
}

const removeFile = (fileName) => {
  uploadedData.value = uploadedData.value.filter((file) => file.name !== fileName)
}
</script>

<style scoped>
.upload-area {
  transition: border-color 0.3s ease-in-out, background-color 0.3s ease-in-out;
}

.upload-area.border-oxford-blue {
  border-color: #1c3a73;
  background-color: #e0e8f9;
}
</style>
