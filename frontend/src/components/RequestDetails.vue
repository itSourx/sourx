<template>
  <div class="pb-5">
    <div class="flex justify-between items-center mb-3 py-2 border-b border-gray-light">
      <span class="text-gray-medium">
        Demande effectuée le : <strong>{{ formatDateString(demande.dateRequest) }}</strong>
      </span>
      <small class="text-gray-medium">
        Réponse reçue le : <strong>{{ formatDateString(demande.dateResponse) }}</strong>
      </small>
    </div>

    <div class="relative py-6 px-0">
      <blockquote class="relative text-gray-medium italic">
        <span class="relative z-10">" {{ demande.description }} "</span>
      </blockquote>
    </div>

    <div v-if="demande.status === 'Refusée'" class="mt-5">
      <h3 class="text-lg font-semibold text-red">Motif de refus :</h3>
      <p class="text-gray-medium">{{ demande.motif }}</p>
    </div>

    <div v-if="demande.documentUrls && demande.documentUrls.length > 0" class="mt-5">
      <div class="flex justify-between items-center mb-3">
        <h3 class="text-lg font-semibold text-gray-medium">Justificatifs soumis :</h3>
        <button @click="downloadAllFiles(demande.documentUrls)" class="text-blue hover:underline">
          Tout télécharger
        </button>
      </div>
      <div class="flex flex-wrap gap-4">
        <div v-for="document in demande.documentUrls" :key="document.url" class="w-full sm:w-1/2 lg:w-1/3 py-2">
          <div
            class="bg-white border border-gray-light rounded-lg p-4 flex items-center shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-center w-10 h-10 rounded-full mr-3">
              <DocumentIcon :fileName="document.filename" class="text-xl mr-4" />
            </div>
            <a :href="document.url" target="_blank" class="flex-grow text-gray-medium truncate group">
              <span class="truncate">{{ document.filename }}</span>
            </a>
            <DownloadIcon
              class="w-5 h-5 text-gray-medium group-hover:text-zaffre transition-colors duration-200 cursor-pointer ml-auto"
              @click.prevent="downloadFile(document.url)" />
          </div>
        </div>
      </div>
    </div>
    <div v-if="
      demande.status === 'Approuvée' &&
      demande.responseAttachement &&
      demande.responseAttachement.length > 0
    " class="mt-5">
      <div class="flex justify-between items-center mb-3">
        <h3 class="text-lg font-semibold text-gray-medium">Pièces jointes de la réponse :</h3>
        <button @click="downloadAllFiles(demande.responseAttachement)" class="text-blue hover:underline">
          Tout télécharger
        </button>
      </div>
      <div class="flex flex-wrap gap-4">
        <div v-for="document in demande.responseAttachement" :key="document.url" class="w-full sm:w-1/2 lg:w-1/3 py-2">
          <div
            class="bg-white border border-gray-light rounded-lg p-4 flex items-center shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-center w-10 h-10 rounded-full mr-3">
              <DocumentIcon :fileName="document.filename" class="text-xl mr-4" />
            </div>
            <a :href="document.url" target="_blank" class="flex-grow text-gray-medium truncate group">
              <span class="truncate">{{ document.filename }}</span>
            </a>
            <DownloadIcon
              class="w-5 h-5 text-gray-medium group-hover:text-zaffre transition-colors duration-200 cursor-pointer ml-auto"
              @click.prevent="downloadFile(document.url)" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { formatDateString } from '@/utils'
import JSZip from 'jszip'

const props = defineProps({
  demande: {
    type: Object,
    required: true
  }
})

function downloadFile(url) {
  window.open(url, '_blank')
}

async function downloadAllFiles(files) {
  const zip = new JSZip()
  const folder = zip.folder('documents')

  const requests = files.map(async (document) => {
    const response = await fetch(document.url)
    const blob = await response.blob()
    folder.file(document.filename, blob)
  })

  await Promise.all(requests)

  const content = await zip.generateAsync({ type: 'blob' })
  const url = URL.createObjectURL(content)

  const a = document.createElement('a')
  a.href = url
  a.download = 'documents.zip'
  a.click()

  URL.revokeObjectURL(url)
}
</script>

<style scoped>
.pb-5 {
  padding-bottom: 1.25rem;
}

.bg-gray-100 {
  background-color: #f3f4f6;
}
</style>
