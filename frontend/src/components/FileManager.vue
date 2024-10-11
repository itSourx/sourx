<template>
    <div class="p-4">
        <!-- Barre de recherche -->
        <div class="flex justify-between items-center mb-4">
            <label class="flex items-center space-x-2">
                <input type="checkbox" v-model="showOnlyFiles" class="form-checkbox text-oxford-blue" />
                <span class="text-gray-medium">Afficher uniquement les fichiers</span>
            </label>

            <div class="relative">
                <input v-model="searchQuery" type="text" placeholder="Rechercher..."
                    class="border border-gray-light text-gray-medium text-xs rounded-md px-5 py-3 pl-10 focus:outline-none" />
                <SearchIcon class="absolute inset-y-0 left-3 p-1 my-auto text-xs text-gray-medium" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <div v-if="currentFolder" class="mb-4">
                <button @click="navigateBack" class="text-oxford-blue hover:underline mb-4">
                    ← Retour
                </button>
                <h2 class="text-xl font-semibold mb-4">{{ currentFolder.name }}</h2>

                <button @click="addDocument(currentFolder.name)" v-if="currentFolder.creatorRole !== 'Administrateur'"
                    class="flex items-center bg-oxford-blue hover:bg-zaffre text-white font-bold px-4 py-2 rounded-md shadow-md">
                    <PlusCircleIcon class="mr-2" />
                    Ajouter un fichier
                </button>
            </div>
            <table class="min-w-full bg-white border border-gray-light">
                <thead class="bg-gray-light border-b">
                    <tr>
                        <th class="p-4 text-left text-xs font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
                            @click="sortBy('name')">
                            Nom
                            <ArrowUpNarrowWide v-if="sortKey === 'name' && sortOrder === 'asc'"
                                class="w-4 h-4 text-gray-medium inline" />
                            <ArrowDownNarrowWide v-if="sortKey === 'name' && sortOrder === 'desc'"
                                class="w-4 h-4 text-gray-medium inline" />
                        </th>
                        <th class="p-4 text-left text-xs font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
                            @click="sortBy('sender')">
                            Créateur
                            <ArrowUpNarrowWide v-if="sortKey === 'sender' && sortOrder === 'asc'"
                                class="w-4 h-4 text-gray-medium inline" />
                            <ArrowDownNarrowWide v-if="sortKey === 'sender' && sortOrder === 'desc'"
                                class="w-4 h-4 text-gray-medium inline" />
                        </th>
                        <th class="p-4 text-left text-xs font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
                            @click="sortBy('documentCount')">
                            {{ showOnlyFiles ? 'Créé le' : 'Nbr documents' }}
                            <ArrowUpNarrowWide v-if="sortKey === 'documentCount' && sortOrder === 'asc'"
                                class="w-4 h-4 text-gray-medium inline" />
                            <ArrowDownNarrowWide v-if="sortKey === 'documentCount' && sortOrder === 'desc'"
                                class="w-4 h-4 text-gray-medium inline" />
                        </th>
                        <th class="p-4 text-left text-xs font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
                            @click="sortBy('size')">
                            Taille
                            <ArrowUpNarrowWide v-if="sortKey === 'size' && sortOrder === 'asc'"
                                class="w-4 h-4 text-gray-medium inline" />
                            <ArrowDownNarrowWide v-if="sortKey === 'size' && sortOrder === 'desc'"
                                class="w-4 h-4 text-gray-medium inline" />
                        </th>
                        <th v-if="showOnlyFiles" @click="sortBy('folderName')"
                            class="p-4 text-left text-xs font-medium text-gray-medium uppercase tracking-wider cursor-pointer">
                            Dossier
                            <ArrowUpNarrowWide v-if="sortKey === 'folderName' && sortOrder === 'asc'"
                                class="w-4 h-4 text-gray-medium inline" />
                            <ArrowDownNarrowWide v-if="sortKey === 'folderName' && sortOrder === 'desc'"
                                class="w-4 h-4 text-gray-medium inline" />
                        </th>
                        <th class="p-4 text-left text-xs font-medium text-gray-medium uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dossiers -->
                    <tr v-for="folder in paginatedFolders" :key="folder.id" v-if="!showOnlyFiles"
                        class="bg-white text-sm border-b border-gray-light">
                        <td class="p-4 flex items-center">

                            <DocumentIcon :fileName="folder.name" :isFolder="true" class="text-xl mr-4" />
                            <span @click="openFolder(folder)" class="cursor-pointer text-oxford-blue">{{
                                folder.name
                                }}</span>
                        </td>
                        <td class="p-4 text-gray-medium">
                            {{
                                folder.sender === props.user.nom + ' ' + props.user.prenom ? 'Moi' : folder.sender
                            }}
                        </td>
                        <td class="p-4 text-gray-medium text-left">{{ folder.files.length }}</td>
                        <td class="p-4 text-gray-medium">{{ folder.size.toFixed(2) }} MB</td>
                        <td class="p-4 flex items-center justify-center" v-if="folder.creatorRole !== 'Administrateur'">
                            <div class="relative">
                                <button @click="toggleActionsMenu(folder.id)" class="focus:outline-none">
                                    <EllipsisVertical class="w-5 h-5 text-oxford-blue" />
                                </button>

                                <div v-if="showActionsMenu === folder.id"
                                    class="absolute right-0 mt-2 w-40 bg-white border border-gray-light rounded-md shadow-lg z-10"
                                    @click.stop>
                                    <ul class="py-1">
                                        <li>
                                            <button @click="addDocument(folder.name)"
                                                class="block px-4 py-2 text-sm text-oxford-blue hover:bg-gray-light w-full text-left">
                                                <PlusCircleIcon class="inline w-4 h-4 mr-2" />
                                                Ajouter
                                            </button>
                                        </li>
                                        <li>
                                            <button @click="renameFolder(folder)"
                                                class="block px-4 py-2 text-sm text-oxford-blue hover:bg-gray-light w-full text-left">
                                                <Pencil class="inline w-4 h-4 mr-2" />
                                                Renommer
                                            </button>
                                        </li>
                                        <li>
                                            <button @click="removeFolder(folder)"
                                                class="block px-4 py-2 text-sm text-red hover:bg-gray-light w-full text-left">
                                                <Trash class="inline w-4 h-4 mr-2" />
                                                Supprimer
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Fichiers -->
                    <tr v-for="file in paginatedFiles" :key="file.id"
                        class="bg-white text-sm border-b border-gray-light">
                        <td class="p-4 flex items-center text-oxford-blue">

                            <DocumentIcon :fileName="file.name" class="text-xl mr-4" />
                            <span class="cursor-pointer hover:underline" @click="openFileInNewTab(file.url)">
                                {{ file.name.split('.').slice(0, -1).join('.') }}
                            </span>
                        </td>
                        <td class="p-4 text-gray-medium">
                            {{ file.sender === props.user.nom + ' ' + props.user.prenom ? 'Moi' : file.sender }}
                        </td>
                        <td class="p-4 text-gray-medium">{{ formatDateString(file.dateAdded) }}</td>
                        <td class="p-4 text-gray-medium">{{ file.size }}</td>
                        <td v-if="showOnlyFiles" class="p-4 text-gray-medium">{{ file.folderName }}</td>
                        <td class="p-4 text-gray-medium flex space-x-2">
                            <DownloadIcon class="w-4 h-4 text-gray-medium cursor-pointer hover:text-oxford-blue"
                                @click="downloadFile(file)" />
                            <template v-if="currentFolder && currentFolder.creatorRole !== 'Administrateur'">
                                <Replace class="w-4 h-4 text-gray-medium cursor-pointer hover:text-green"
                                    @click="selectFileToMove(file)" />
                                <Trash class="w-4 h-4 text-red cursor-pointer hover:text-red"
                                    @click="removeFile(file)" />
                            </template>

                        </td>
                    </tr>

                    <!-- Interface de déplacement des fichiers -->
                    <tr v-if="fileToMove && fileToMove.id" :key="'move-' + fileToMove.id"
                        class="bg-white text-sm border-b border-gray-light">
                        <td colspan="5">
                            <div ref="moveSection" class="mt-6 p-4 border border-gray-light bg-gray-light">
                                <h3 class="text-lg font-semibold mb-4">
                                    Déplacer le fichier: {{ fileToMove.name }}
                                </h3>
                                <label for="folderSelect"
                                    class="block text-sm font-medium text-gray-dark mb-2">Sélectionnez un dossier de
                                    destination :</label>
                                <select id="folderSelect" v-model="selectedFolderId"
                                    class="block w-full p-2 border border-gray-light rounded-lg">
                                    <option v-for="folder in availableFolders" :key="folder.id" :value="folder.id">
                                        {{ folder.name }}
                                    </option>
                                </select>
                                <button @click="moveFile"
                                    class="mt-4 bg-oxford-blue text-white py-2 px-4 rounded-lg hover:bg-zaffre">
                                    Déplacer
                                </button>
                                <button @click="cancelMoveFile"
                                    class="bg-red text-white py-2 px-4 mx-2 rounded-lg hover:bg-red-800">
                                    Annuler
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="folderToRename" :key="'rename-' + folderToRename.id"
                        class="bg-white text-sm border-b border-gray-light">
                        <td colspan="5">
                            <div class="mt-6 p-4 border border-gray-light bg-gray-light">
                                <h3 class="text-lg font-semibold mb-4">Renommer le dossier : {{ folderToRename.name }}
                                </h3>
                                <input v-model="newFolderName" type="text" placeholder="Nouveau nom"
                                    class="w-full p-2 border border-gray-light rounded-lg" />
                                <div class="flex justify-end mt-2">
                                    <button @click="confirmRenameFolder"
                                        class="bg-oxford-blue text-white py-2 px-4 rounded-lg hover:bg-zaffre"
                                        :disabled="!newFolderName.trim()">
                                        Renommer
                                    </button>
                                    <button @click="cancelRenameFolder"
                                        class="bg-red text-white py-2 px-4 mx-2 rounded-lg hover:bg-red-800">
                                        Annuler
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>

            <!-- Message si aucun résultat -->
            <div v-if="showOnlyFiles && paginatedFiles.length === 0 || (!showOnlyFiles && paginatedFolders.length === 0 && paginatedFiles.length === 0)"
                class="text-center text-gray-medium p-4">
                Aucun fichier ou dossier trouvé.
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex justify-end space-x-2">
                <button v-if="currentPage > 1" @click="prevPage" class="text-xs flex items-center">
                    <StepBack class="w-4 h-4" />
                </button>
                <span class="text-xs flex items-center">Page {{ currentPage }} sur {{ totalPages }}</span>
                <button v-if="currentPage < totalPages" @click="nextPage" class="text-xs flex items-center">
                    <StepForward class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { formatDateString } from '@/utils'

// =========================
// Props and Emits
// =========================
const props = defineProps({
    rootFolders: {
        type: Array,
        required: true
    },
    user: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['remove-folder', 'rename-folder', 'remove-file', 'move-file'])

// =========================
// Reactive States and Refs
// =========================
const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = 10
const showOnlyFiles = ref(false)
const localRootFolders = ref([...props.rootFolders])
const showActionsMenu = ref(null)
const currentFolder = ref(null) // Dossier actuellement ouvert
const sortKey = ref('name')
const sortOrder = ref('asc')
const fileToMove = ref(null)
const selectedFolderId = ref(null)
const moveSection = ref(null)
const folderToRename = ref(null)
const newFolderName = ref('')

// =========================
// Watchers
// =========================
watch(
    () => props.rootFolders,
    (newRootFolders) => {
        localRootFolders.value = [...newRootFolders]
    },
    { immediate: true }
)

watch([searchQuery, currentFolder, showOnlyFiles], () => {
    currentPage.value = 1
    if (!currentFolder.value && showOnlyFiles.value) {
        currentFolder.value = null; // Réinitialiser correctement
    }
})

watch(showOnlyFiles, () => {
    if (showOnlyFiles.value) {
        currentFolder.value = null // Réinitialiser le dossier actuel lorsque "Afficher uniquement les fichiers" est activé
    }
})

// =========================
// Computed Properties
// =========================
const filteredFolders = computed(() => {
    if (currentFolder.value || showOnlyFiles.value) return []
    return localRootFolders.value.filter((folder) => {
        const folderName = typeof folder.name === 'object' && folder.name !== null
            ? folder.name.name
            : folder.name

        return folderName.toLowerCase().includes(searchQuery.value.toLowerCase())
    })
})

const filteredFiles = computed(() => {

    console.log("PAGINATED")
    console.log(localRootFolders.value)

    if (showOnlyFiles.value) {
        // Récupérer tous les fichiers de tous les dossiers
        let allFiles = []
        localRootFolders.value.forEach(folder => {
            if (folder.files && folder.files.length > 0) {
                folder.files.forEach(file => {
                    allFiles.push({ ...file, folderName: folder.name }) // Ajoutez le nom du dossier au fichier
                })
            }
        })
        return allFiles.filter((file) =>
            file.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        )
    } else if (currentFolder.value) {
        return currentFolder.value.files.filter(file =>
            file.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }
    return []
})

const sortedFolders = computed(() => {
    return [...filteredFolders.value].sort((a, b) => {
        let result = 0
        if (sortKey.value === 'documentCount') {
            result = a.files.length - b.files.length; // Comparer les longueurs des fichiers
        } else {
            if (a[sortKey.value] < b[sortKey.value]) result = -1
            if (a[sortKey.value] > b[sortKey.value]) result = 1
        }
        return sortOrder.value === 'asc' ? result : -result
    })
})

const sortedFiles = computed(() => {

    return [...filteredFiles.value].sort((a, b) => {
        let result = 0
        if (sortKey.value === 'folderName') {
            if (a.folderName < b.folderName) result = -1
            if (a.folderName > b.folderName) result = 1
        } else {
            if (a[sortKey.value] < b[sortKey.value]) result = -1
            if (a[sortKey.value] > b[sortKey.value]) result = 1
        }
        return sortOrder.value === 'asc' ? result : -result
    })
})

const paginatedFolders = computed(() => {

    console.log("PAGINATED FOLDERS")
    console.log(sortedFolders.value)
    const start = (currentPage.value - 1) * itemsPerPage
    return sortedFolders.value.slice(start, start + itemsPerPage)
})

const paginatedFiles = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    return sortedFiles.value.slice(start, start + itemsPerPage)
})

const totalPages = computed(() => {
    return Math.ceil((sortedFolders.value.length + sortedFiles.value.length) / itemsPerPage)
})

const availableFolders = computed(() => {
    return localRootFolders.value.filter(
        (folder) => !currentFolder.value || folder.id !== currentFolder.value.id
    )
})

// =========================
// Methods
// =========================
const openFileInNewTab = (url) => {
    window.open(url, '_blank')
}

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++
    }
}

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--
    }
}

const sortBy = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortKey.value = key
        sortOrder.value = 'asc'
    }
}

const openFolder = (folder) => {
    currentFolder.value = folder
    currentPage.value = 1 // Réinitialiser la pagination à la première page
}

const navigateBack = () => {
    currentFolder.value = null
    currentPage.value = 1 // Réinitialiser la pagination à la première page
}

const downloadFile = (file) => {
    console.log('Télécharger le fichier:', file.name)
    const link = document.createElement('a')
    link.href = file.url
    link.download = file.name
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
}

const selectFileToMove = (file) => {
    console.log(props.user.nom + ' ' + props.user.prenom)
    console.log(paginatedFolders)

    fileToMove.value = file
    console.log(fileToMove.value)
    selectedFolderId.value = null
    // Scroller vers la section de déplacement des fichiers
    nextTick(() => {
        if (moveSection.value) {
            moveSection.value.scrollIntoView({
                behavior: 'smooth'
            })
        }
    })
}

const moveFile = () => {
    if (!selectedFolderId.value) {
        alert('Veuillez sélectionner un dossier.')
        return
    }

    const targetFolder = localRootFolders.value.find((folder) => folder.id == selectedFolderId.value)
    if (targetFolder && fileToMove.value) {
        emit('move-file', { file: fileToMove.value, targetFolder, currentFolder: currentFolder.value })
        fileToMove.value = null
    } else {
        console.log('Dossier introuvable')
    }
}

const cancelMoveFile = () => {
    fileToMove.value = null
}

const removeFolder = (folder) => {
    emit('remove-folder', folder)
}

const addDocument = (folderName) => {
    emit('add-document', folderName)
}

const renameFolder = (folder) => {
    folderToRename.value = folder
    newFolderName.value = folder.name
}

const confirmRenameFolder = () => {
    if (newFolderName.value.trim()) {
        emit('rename-folder', folderToRename.value, newFolderName.value.trim())
        folderToRename.value = null
        newFolderName.value = ''
    } else {
        alert('Le nom du dossier ne peut pas être vide.')
    }
}

const cancelRenameFolder = () => {
    folderToRename.value = null
    newFolderName.value = ''
}

const removeFile = (file) => {
    emit('remove-file', file)
}

const toggleActionsMenu = (folderId) => {
    showActionsMenu.value = showActionsMenu.value === folderId ? null : folderId
}

// Fermer le menu quand on clique en dehors (optionnel, pour une meilleure UX)
const closeActionsMenu = (event) => {
    if (!event.target.closest('.relative')) {
        showActionsMenu.value = null
    }
}

onMounted(() => {
    // Fermer le menu d'actions quand on clique en dehors
    document.addEventListener('click', closeActionsMenu)
})

onUnmounted(() => {
    document.removeEventListener('click', closeActionsMenu)
})

</script>