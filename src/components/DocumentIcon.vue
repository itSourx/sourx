<template>
    <img :src="iconSrc" :alt="fileName" class="document-icon" />
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps(['fileName']);

const getFileExtension = (fileName) => {
    if (!fileName) return 'default';
    if (!fileName.includes('.')) return 'folder';
    const parts = fileName.split('.');
    return parts.length > 1 ? parts.pop().toLowerCase() : 'default';
};

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
        case 'py':
        case 'js':
        case 'html':
        case 'css':
        case 'json':
        case 'ts':
        case 'java':
        case 'cpp':
        case 'c':
        case 'php':
        case 'rb':
        case 'cs':
        case 'go':
        case 'sh':
        case 'md':
            return new URL('@/assets/DocumentsIcons/code.png', import.meta.url).href;
        case 'csv':
            return new URL('@/assets/DocumentsIcons/csv.png', import.meta.url).href;
        case 'zip':
        case 'rar':
        case '7z':
            return new URL('@/assets/DocumentsIcons/zip.png', import.meta.url).href;
        case 'odt':
            return new URL('@/assets/DocumentsIcons/odt.png', import.meta.url).href;
        case 'svg':
            return new URL('@/assets/DocumentsIcons/svg.png', import.meta.url).href;
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
        case 'bmp':
        case 'tiff':
            return new URL('@/assets/DocumentsIcons/photo.png', import.meta.url).href;
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