<template>
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-4">
            <button @click="openCreatePosteModal"
                class="flex items-center justify-center bg-oxford-blue text-white py-2 px-4 rounded-md">
                <PlusCircleIcon class="w-4 h-4 mr-2" />
                Créer un Poste
            </button>
            <div class="relative">
                <input v-model="searchQuery" type="text" placeholder="Rechercher un poste..."
                    class="border border-gray-light text-gray-medium text-xs rounded-md px-5 py-3 pl-10 focus:outline-none" />
                <SearchIcon class="absolute inset-y-0 left-3 p-1 my-auto text-xs text-gray-medium" />
            </div>
        </div>

        <div v-if="!posteStore.showLoader" class="overflow-x-auto bg-white shadow-md">
            <table class="min-w-full bg-white border border-gray-light">
                <thead class="bg-gray-light border-b">
                    <tr>
                        <th class="p-4 text-left text-xs font-medium text-gray-medium uppercase tracking-wider">Nom du
                            Poste</th>
                        <th class="p-4 text-left text-xs font-medium text-gray-medium uppercase tracking-wider">Nbr
                            collaborateurs</th>
                        <th class="p-4 text-left text-xs font-medium text-gray-medium uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="poste in filteredPostes" :key="poste.id"
                        class="bg-white text-sm border-b border-gray-light">
                        <td class="p-4 text-gray-medium">{{ poste.name }}</td>
                        <td class="p-4 text-gray-medium">{{ poste.employees.length }}</td>
                        <td class="p-4 flex space-x-2">
                            <button @click="openEditPosteModal(poste)"
                                class="text-blue hover:text-oxford-blue">Modifier</button>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" :checked="poste.isArchived === 0"
                                    @change="togglePosteArchiveStatus(poste)" />
                                <div
                                    class="relative w-11 h-6 bg-gray-light peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-zaffre peer-checked:bg-zaffre rounded-full peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-oxford-blue after:border after:rounded-full after:h-5 after:w-5 after:transition-all">
                                </div>
                                <span class="ms-3 text-sm font-medium text-gray-medium">{{ poste.isArchived === 1 ?
                                    'Archivé' : 'Actif' }}</span>
                            </label>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal pour créer ou modifier un poste -->
        <ModalVue :isOpen="isPosteModalOpen" :title="isEditing ? 'Modifier Poste' : 'Créer un Poste'"
            @close="closePosteModal">
            <form @submit.prevent="savePoste" class="flex flex-col space-y-4">
                <input v-model="form.name" type="text" placeholder="Nom du Poste"
                    class="border border-gray-light rounded-lg p-2 focus:outline-none focus:ring-2 transition"
                    required />
                <button type="submit"
                    class="bg-oxford-blue text-white py-2 rounded-md hover:bg-zaffre transition duration-200">
                    Sauvegarder
                </button>
            </form>
        </ModalVue>

        <LoaderComponent v-if="posteStore.showLoader" class="mt-4" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePosteStore } from '@/stores/UserStore/PosteStore';
import { useUserStore } from '@/stores/UserStore/UserStore'
import ModalVue from '@/components/ModalVue.vue';
import LoaderComponent from '@/components/LoaderComponent.vue';

const posteStore = usePosteStore();
const userStore = useUserStore()

onMounted(async () => {
    await posteStore.fetchPostes();
});

const searchQuery = ref('');
const isPosteModalOpen = ref(false);
const isEditing = ref(false);
const form = ref({ id: null, name: '', employee_ids: [userStore.user.system_id] });

const filteredPostes = computed(() => {
    return posteStore.postes.filter((poste) =>
        poste.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const openCreatePosteModal = () => {
    form.value = { id: null, name: '', employee_ids: [] };
    isEditing.value = false;
    isPosteModalOpen.value = true;
};

const openEditPosteModal = (poste) => {
    form.value = { id: poste.id, name: poste.name, employee_ids: poste.employees };
    isEditing.value = true;
    isPosteModalOpen.value = true;
};

const savePoste = async () => {
    if (isEditing.value) {
        await posteStore.updatePoste(form.value.id, form.value);
    } else {
        await posteStore.createPoste(form.value);
    }
    closePosteModal();
};

const togglePosteArchiveStatus = async (poste) => {
    if (poste.isArchived === 1) {
        await posteStore.unarchivePoste(poste.id);
    } else {
        if (poste.employees.length > 0) {
            if (confirm(`Ce poste a ${poste.employees.length} employé(s) assigné(s). Êtes-vous sûr de vouloir archiver ce poste ?`)) {
                await posteStore.archivePoste(poste.id);
            }
        } else {
            await posteStore.archivePoste(poste.id);
        }
    }
};

const closePosteModal = () => {
    isPosteModalOpen.value = false;
};
</script>
