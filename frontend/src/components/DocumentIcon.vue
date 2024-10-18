<template>
  <img :src="iconSrc" alt="Document Icon" class="w-6 h-6 mr-2" />
</template>

<script setup>
import { computed } from 'vue'
import pdfIcon from '@/assets/DocumentIcons/pdf.png';
import docIcon from '@/assets/DocumentIcons/doc.png';
import xlsIcon from '@/assets/DocumentIcons/xls.png';
import officeIcon from '@/assets/DocumentIcons/office.png';
import txtIcon from '@/assets/DocumentIcons/txt.png';
import defaultIcon from '@/assets/DocumentIcons/default.png';

const props = defineProps({
  fileName: {
    type: String,
    required: true
  },
  isFolder: {
    type: Boolean,
    default: false
  }
})

const useDocumentIcon = (fileName, isFolder) => {
  if (isFolder) {
    return '@/assets/DocumentIcons/folder_document_icon.svg'
  }

  const fileExtension = fileName.split('.').pop().toLowerCase()

  switch (fileExtension) {
    case 'pdf':
      return pdfIcon
    case 'doc':
    case 'docx':
      return docIcon
    case 'xls':
    case 'xlsx':
      return xlsIcon
    case 'ppt':
    case 'pptx':
      return officeIcon
    case 'txt':
      return txtIcon
    default:
      return defaultIcon
  }
}

const iconSrc = computed(() => {
  return useDocumentIcon(props.fileName, props.isFolder)
})
</script>
