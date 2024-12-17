<template>
    <img :src="iconSrc" :alt="fileName" class="document-icon" />
</template>

<script setup>
import { computed } from 'vue';

// Props pour passer le nom du fichier
const props = defineProps(['fileName']);

const getFileExtension = (fileName) => {
    if (!fileName) return 'default';
    if (!fileName.includes('.')) return 'folder';
    const parts = fileName.split('.');
    return parts.length > 1 ? parts.pop().toLowerCase() : 'default';
};

// Computed property pour déterminer l'icône basée sur l'extension de fichier
const iconSrc = computed(() => {
    const ext = getFileExtension(props.fileName);

    switch (ext) {
        case 'doc':
        case 'docx':
            return new URL('@/assets/DocumentsIcons/doc.png', import.meta.url).href;
        case 'ppt':
        case 'pptx':
            return new URL('@/assets/DocumentsIcons/ppt.png', import.meta.url).href;
        case 'xls':
        case 'xlsx':
            return new URL('@/assets/DocumentsIcons/xls.png', import.meta.url).href;
        case 'txt':
            return new URL('@/assets/DocumentsIcons/txt.png', import.meta.url).href;
        case 'pdf':
            return new URL('@/assets/DocumentsIcons/pdf.png', import.meta.url).href;
        case 'folder':
            return new URL('@/assets/DocumentsIcons/folder.png', import.meta.url).href;
        default:
            return new URL('@/assets/DocumentsIcons/default.png', import.meta.url).href;
    }
});
</script>

<style scoped>
.document-icon {
    width: 24px;
    height: 24px;
    margin-right: 10px;
    vertical-align: middle;
}
</style>