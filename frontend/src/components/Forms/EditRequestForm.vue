<template>
    <div v-if="formInitialized">
        <form @submit.prevent="handleSubmit">
            <!-- <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Titre de la demande</label>
                <input type="text" id="title" v-model="form.title"
                    class="mt-1 block w-full px-3 py-2 border border-gray-light rounded-md shadow-sm focus:outline-none focus:ring-blue focus:border-blue"
                    :disabled="!isEditable" />
            </div> -->


            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Titre de la demande</label>
                <select id="motif" v-model="form.motif"
                    class="mt-1 block w-full px-3 py-2 border border-gray-light rounded-md shadow-sm focus:outline-none focus:ring-blue focus:border-blue">
                    <option value="" disabled selected>--Choisir un motif--</option>
                    <option v-for="motif in demandsStore.motifs" :key="motif.id" :value="motif.id">
                        {{ motif.title }}
                    </option>
                </select>
            </div>


            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" v-model="form.description" rows="3"
                    class="mt-1 block w-full px-3 py-2 border border-gray-light rounded-md shadow-sm focus:outline-none focus:ring-blue focus:border-blue"
                    :disabled="!isEditable"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Pièces jointes</label>
                <div class="upload-area py-16 border-2 border-dashed bg-gray-light cursor-pointer hover:border-blue hover:text-blue mt-2"
                    @dragover.prevent @drop="handleDrop" @click="clickFileInput"
                    :class="{ 'opacity-50 cursor-not-allowed': !isEditable }">
                    <p class="text-center">Déposez vos fichiers ici ou cliquez pour les téléverser.</p>
                    <input id="fileInput" type="file" ref="fileInput" @change="handleFileChange" class="hidden" multiple
                        :disabled="!isEditable" />
                </div>
                <div v-if="uploadedFiles.length > 0" class="mt-2">
                    <UploadFileList v-for="(file, index) in uploadedFiles" :key="index" :file-name="file.filename"
                        :remove-file="removeFile" />
                </div>
            </div>

            <LoaderComponent v-if="isLoading" />

            <button type="submit"
                class="bg-oxford-blue hover:bg-zaffre w-full text-white py-4 px-4 rounded-md shadow-sm hover:bg-blue-dark"
                :disabled="!isEditable">
                Enregistrer les modifications
            </button>
        </form>
    </div>
    <div v-else>
        <p>Chargement de la demande...</p>
    </div>
</template>

<script setup>
import { ref, reactive, watchEffect } from 'vue';
import { toast } from 'vue3-toastify';
import { useDemandStore } from '@/stores/UserStore/DemandStore'
import UploadFileList from '@/components/UploadFileList.vue';
import LoaderComponent from '@/components/LoaderComponent.vue';

const isLoading = ref(false);
const demandsStore = useDemandStore()
const formInitialized = ref(false);

// Props et événements
const props = defineProps({
    demande: {
        type: Object,
        required: true,
        default: null,
    },
});

const emit = defineEmits(['save']);

const form = reactive({
    title: '',
    description: '',
    status: '',
    motif: '',
});

const uploadedFiles = ref([]);
const isEditable = ref(false);
const fileInput = ref(null);

watchEffect(() => {

    if (props.demande) {

        console.log(props.demande)

        form.title = props.demande.title;
        form.description = props.demande.description;
        form.status = props.demande.status;

        const matchingMotif = demandsStore.motifs.find(motif => motif.title === props.demande.title);
        if (matchingMotif) {
            form.motif = matchingMotif.id;
        }


        uploadedFiles.value = [...props.demande.documentUrls.map(doc => ({
            filename: doc.name || doc.filename,
            url: doc.url
        }))];
        isEditable.value = props.demande.status === 'Soumis';
        formInitialized.value = true;
    }
});

const handleSubmit = () => {
    if (!isEditable.value) {
        toast.error("Cette demande ne peut pas être modifiée car elle n'est pas en statut 'Soumis'.");
        return;
    }

    isLoading.value = true;

    try {
        const updatedRequest = {
            ...props.demande,
            title: form.motif,
            description: form.description,
            documentUrls: uploadedFiles.value,
        };

        console.log(updatedRequest)

        emit('save', updatedRequest);
    } catch (error) {
        console.error('Erreur lors de la mise à jour de la demande:', error);
    } finally {
        isLoading.value = false;
    }
};

const handleDrop = (event) => {
    event.preventDefault();
    if (!isEditable.value) return;

    const files = Array.from(event.dataTransfer.files);
    files.forEach(file => uploadedFiles.value.push({
        filename: file.name,
        url: URL.createObjectURL(file)
    }));
};

const handleFileChange = (event) => {
    if (!isEditable.value) return;

    const files = Array.from(event.target.files);
    files.forEach(file => uploadedFiles.value.push({
        filename: file.name,
        url: URL.createObjectURL(file)
    }));
};

const clickFileInput = () => {
    if (!isEditable.value) return;
    if (fileInput.value) fileInput.value.click();  // Use the ref instead of document.getElementById
};


const removeFile = (fileName) => {
    if (!isEditable.value) return;
    uploadedFiles.value = uploadedFiles.value.filter((file) => file.filename !== fileName);
};

</script>

<style scoped>
.upload-area {
    transition: border-color 0.3s, color 0.3s;
}

.upload-area:hover {
    border-color: #2563eb;
    color: #2563eb;
}
</style>