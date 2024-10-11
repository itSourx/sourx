<template>
    <div>
        <button @click="openCreateMotifModal"
            class="flex items-center justify-center bg-oxford-blue text-white py-2 px-4 rounded-md">
            <PlusCircleIcon class="w-4 h-4 mr-2" />
            Ajouter un Motif
        </button>

        <table class="min-w-full bg-white border border-gray-light mt-4">
            <thead>
                <tr>
                    <th class="p-4 text-left">Titre</th>
                    <th class="p-4 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="motif in motifs" :key="motif.id">
                    <td class="p-4">{{ motif.title }}</td>
                    <td class="p-4 flex space-x-2">
                        <button @click="openEditMotifModal(motif)" class="text-blue">Modifier</button>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" :checked="motif.isArchived === 0"
                                @change="toggleArchiveStatus(motif)" />
                            <div
                                class="relative w-11 h-6 bg-gray-light peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-zaffre peer-checked:bg-zaffre rounded-full peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-oxford-blue after:border after:rounded-full after:h-5 after:w-5 after:transition-all">
                            </div>
                            <span class="ms-3 text-sm font-medium text-gray-medium">{{ motif.isArchived === 1 ?
                                'Archivé' : 'Actif' }}</span>
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>

        <ModalVue v-if="isMotifModalOpen" :isOpen="isMotifModalOpen"
            :title="form.id ? 'Modifier Motif' : 'Ajouter Motif'" @close="closeMotifModal">
            <form @submit.prevent="saveMotif" class="flex flex-col space-y-4">
                <input v-model="form.title" type="text" placeholder="Titre du motif"
                    class="border border-gray-light rounded-lg p-2 focus:outline-none focus:ring-2 transition"
                    required />
                <button type="submit"
                    class="bg-oxford-blue text-white py-2 rounded-md hover:bg-zaffre transition duration-200">
                    Sauvegarder
                </button>
            </form>
        </ModalVue>

        <LoaderComponent v-if="isLoading" class="mt-4" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useDemandStore } from '@/stores/UserStore/DemandStore';
import ModalVue from '@/components/ModalVue.vue';
import LoaderComponent from '@/components/LoaderComponent.vue';

const demandStore = useDemandStore();
let motifs = demandStore.motifs;
const isMotifModalOpen = ref(false);
const form = ref({ id: null, title: '' });
const isLoading = ref(false);

const fetchMotifs = async () => {
    isLoading.value = true;
    await demandStore.fetchMotifs();
    motifs = demandStore.motifs;
    isLoading.value = false;
};

const openCreateMotifModal = () => {
    form.value = { id: null, title: '' };
    isMotifModalOpen.value = true;
};

const openEditMotifModal = (motif) => {
    form.value = { id: motif.id, title: motif.title };
    isMotifModalOpen.value = true;
};

const saveMotif = async () => {
    isLoading.value = true;
    if (form.value.id) {
        await demandStore.updateMotif(form.value.id, form.value.title);
    } else {
        await demandStore.createMotif(form.value.title);
    }
    await fetchMotifs();
    closeMotifModal();
    isLoading.value = false;
};

const toggleArchiveStatus = async (motif) => {
    isLoading.value = true;

    try {
        if (motif.isArchived === 1) {
            await demandStore.unarchiveMotif(motif.id);
            motif.isArchived = 0; // Mise à jour locale après succès
        } else {
            await demandStore.archiveMotif(motif.id);
            motif.isArchived = 1;
        }
        toast.success('Statut modifié avec succès');
    } catch (error) {
        toast.error('Erreur lors de la modification du statut');
    } finally {
        await fetchMotifs();
        isLoading.value = false;
    }
};

const closeMotifModal = () => {
    isMotifModalOpen.value = false;
};

onMounted(async () => {
    await fetchMotifs();
});
</script>
