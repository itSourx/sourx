<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Gestion des Dossiers et Documents</h1>

    <!-- Barre de recherche et actions -->
    <div class="flex justify-between items-center mb-4">
      <div class="relative w-1/3">
        <input v-model="searchQuery" type="text" placeholder="Rechercher..."
          class="border border-gray-light text-gray-medium text-xs rounded-md px-5 py-3 pl-10 focus:outline-none" />
        <SearchIcon class="absolute inset-y-0 left-3 p-1 my-auto text-xs text-gray-medium" />
      </div>
      <div class="flex space-x-4">
        <label class="flex items-center space-x-2">
          <input type="checkbox" v-model="showOnlyFiles" class="form-checkbox text-oxford-blue" />
          <span class="text-gray-medium">Afficher uniquement les fichiers</span>
        </label>
        <!-- <button @click="openCreateFolderModal"
          class="flex items-center bg-oxford-blue hover:bg-zaffre text-white font-bold px-4 py-2 rounded-md shadow-md">
          <PlusCircleIcon class="mr-2" />
          Nouveau Dossier
        </button> -->
        <button @click="openUploadModal"
          class="flex items-center bg-oxford-blue hover:bg-zaffre text-white font-bold px-4 py-2 rounded-md shadow-md">
          <PlusCircleIcon class="mr-2" />
          Nouveau Document
        </button>
      </div>
    </div>

    <div v-if="currentFolder && !showOnlyFiles" class="mb-4">
      <button @click="navigateBack" class="text-oxford-blue hover:underline mb-4">← Retour</button>
      <h2 class="text-xl font-semibold mb-4">{{ currentFolder.name.name }}</h2>
    </div>

    <!-- Table des dossiers et fichiers -->
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
            @click="sortBy('dateAdded')">
            Date d'ajout
            <ArrowUpNarrowWide v-if="sortKey === 'dateAdded' && sortOrder === 'asc'"
              class="w-4 h-4 text-gray-medium inline" />
            <ArrowDownNarrowWide v-if="sortKey === 'dateAdded' && sortOrder === 'desc'"
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
          <th v-if="showOnlyFiles"
            class="p-4 text-left text-xs font-medium text-gray-medium uppercase tracking-wider cursor-pointer"
            @click="sortBy('folderName')">
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
        <tr v-if="!showOnlyFiles" v-for="folder in paginatedFolders" :key="folder.id"
          class="bg-white text-sm border-b border-gray-light">
          <td class="p-4 flex items-center">
            <DocumentIcon :fileName="folder.name.name" :isFolder="true" class="text-xl mr-4" />
            <span @click="openFolder(folder)" class="cursor-pointer text-oxford-blue">
              {{ folder.name.name }}
            </span>
          </td>
          <td class="p-4 text-gray-medium">{{ folder.dateAdded }}</td>
          <td class="p-4 text-gray-medium">--</td>
          <td class="p-4 text-gray-medium flex space-x-2">
            <Edit3 class="w-4 h-4 text-gray-medium cursor-pointer hover:text-oxford-blue"
              @click="openRenameModal(folder, 'folder')" />
            <Trash class="w-4 h-4 text-red cursor-pointer hover:text-red-700" @click="removeFolder(folder)" />
          </td>
        </tr>

        <!-- Fichiers dans le dossier sélectionné -->
        <tr v-if="currentFolder && !showOnlyFiles" v-for="file in currentFolder.files" :key="file.id"
          class="bg-white text-sm border-b border-gray-light">
          <td class="p-4 flex items-center text-oxford-blue cursor-pointer hover:underline" @click="downloadFile(file)">
            <DocumentIcon :fileName="file.name" class="text-xl mr-4" />
            {{ file.name.split('.').slice(0, -1).join('.') }}
          </td>
          <td class="p-4 text-gray-medium">{{ file.dateAdded }}</td>
          <td class="p-4 text-gray-medium">{{ file.size }}</td>
          <td class="p-4 text-gray-medium flex space-x-2">
            <DownloadIcon class="w-4 h-4 text-gray-medium cursor-pointer hover:text-oxford-blue"
              @click="downloadFile(file)" />
            <!-- <Edit3 class="w-4 h-4 text-gray-medium cursor-pointer hover:text-oxford-blue"
              @click="openRenameModal(file, 'file')" /> -->
            <Trash class="w-4 h-4 text-red cursor-pointer hover:text-red-700" @click="removeFile(file)" />
          </td>
        </tr>

        <!-- Fichiers -->
        <tr v-for="file in paginatedFiles" :key="file.id" v-if="showOnlyFiles"
          class="bg-white text-sm border-b border-gray-light">
          <td class="p-4 flex items-center text-oxford-blue">
            <DocumentIcon :fileName="file.name" class="text-xl mr-4" />
            {{ file.name.split('.').slice(0, -1).join('.') }}
          </td>
          <td class="p-4 text-gray-medium">{{ file.dateAdded }}</td>
          <td class="p-4 text-gray-medium">{{ file.size }}</td>
          <td v-if="showOnlyFiles" class="p-4 text-gray-medium">{{ file.folderName }}</td>
          <td class="p-4 text-gray-medium flex space-x-2">
            <DownloadIcon class="w-4 h-4 text-gray-medium cursor-pointer hover:text-oxford-blue"
              @click="downloadFile(file)" />
            <!-- <Edit3 class="w-4 h-4 text-gray-medium cursor-pointer hover:text-oxford-blue"
              @click="openRenameModal(file, 'file')" /> -->
            <Trash class="w-4 h-4 text-red cursor-pointer" @click="removeFile(file)" />
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Message si aucun résultat -->
    <div v-if="paginatedFolders.length === 0 && paginatedFiles.length === 0" class="text-center text-gray-medium p-4">
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

    <!-- Modals -->
    <ModalVue :isOpen="isCreateFolderModalOpen" title="Nouveau Dossier" @close="handleModalClose">
      <div class="p-4">
        <input v-model="newFolderName" type="text" placeholder="Nom du dossier"
          class="w-full border border-gray-light rounded-md px-4 py-2 focus:outline-none" />
        <div class="flex justify-end mt-4">
          <button @click="createFolder"
            class="bg-oxford-blue text-white px-4 py-2 rounded-md shadow-md hover:bg-zaffre">
            Créer
          </button>
        </div>
      </div>
    </ModalVue>

    <ModalVue :isOpen="isRenameModalOpen" title="Renommer" @close="handleModalClose">
      <div class="p-4">
        <input v-model="renameValue" type="text" :placeholder="`Nouveau nom du ${renameType}`"
          class="w-full border border-gray-light rounded-md px-4 py-2 focus:outline-none" />
        <div class="flex justify-end mt-4">
          <button @click="renameItem" class="bg-oxford-blue text-white px-4 py-2 rounded-md shadow-md hover:bg-zaffre">
            Renommer
          </button>
        </div>
      </div>
    </ModalVue>

    <ModalVue :isOpen="isUploadModalOpen" title="Ajouter un document" @close="handleModalClose">
      <UploadAreaAdmin @upload-complete="handleUploadComplete" />
    </ModalVue>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import ModalVue from '@/components/ModalVue.vue'
import UploadAreaAdmin from '@/components/UploadAreaAdmin.vue'

const props = defineProps({
  files: {
    type: Array,
    required: true
  },
  folders: {
    type: Array,
    required: true
  }
})

const emit = defineEmits([
  'create-folder',
  'rename-item',
  'remove-folder',
  'remove-file',
  'upload-document'
])

const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = 10
const showOnlyFiles = ref(true)

const isCreateFolderModalOpen = ref(false)
const isRenameModalOpen = ref(false)
const isUploadModalOpen = ref(false)

const currentFolder = ref(null)
const sortKey = ref('name')
const sortOrder = ref('asc')
const renameValue = ref('')
const renameType = ref('')
const itemToRename = ref(null)
const newFolderName = ref('')


const groupFoldersByName = computed(() => {
  const folderMap = new Map();

  props.folders.forEach(folder => {
    console.log(folder)
    const folderName = folder.name.name.toLowerCase();

    if (!folderMap.has(folderName)) {
      folderMap.set(folderName, {
        ...folder,
        documents: [...folder.documents], // Clone the documents array
      });
    } else {
      // If the folder name already exists, merge the documents
      const existingFolder = folderMap.get(folderName);
      existingFolder.documents = [...existingFolder.documents, ...folder.documents];
    }
  });

  return Array.from(folderMap.values());
});

const filteredFolders = computed(() => {
  if (showOnlyFiles.value || currentFolder.value) {
    return [];
  }

  return groupFoldersByName.value.filter((folder) =>
    folder.name.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const filteredFiles = computed(() => {
  if (showOnlyFiles.value) {
    const allFiles = props.folders.reduce((files, folder) => {
      if (folder.documents && folder.documents.length > 0) {
        folder.documents.forEach(file => {
          files.push({ ...file, folderName: folder.name.name });
        });
      }
      return files;
    }, []);
    return allFiles.filter((file) =>
      file.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }
  else {
    const files = currentFolder.value ? currentFolder.value.files : props.files;
    return files.filter((file) =>
      file.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }
});

const sortedFolders = computed(() => {
  return [...filteredFolders.value].sort((a, b) => {
    let result = 0
    const valueA = a.name.name.toLowerCase();
    const valueB = b.name.name.toLowerCase();

    if (valueA < valueB) result = -1
    if (valueA > valueB) result = 1
    return sortOrder.value === 'asc' ? result : -result
  })
});

const sortedFiles = computed(() => {
  return [...filteredFiles.value].sort((a, b) => {
    let result = 0
    if (a[sortKey.value] < b[sortKey.value]) result = -1
    if (a[sortKey.value] > b[sortKey.value]) result = 1
    return sortOrder.value === 'asc' ? result : -result
  })
})

const paginatedFolders = computed(() => {
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
};

const openFolder = (folder) => {
  const files = folder.documents
    ? folder.documents.map(doc => ({
      id: doc.id,
      name: doc.name,
      dateAdded: doc.dateAdded,
      size: doc.size,
      url: doc.url,
    }))
    : [];


  currentFolder.value = {
    ...folder,
    files: files
  };

  console.log('Opening folder:', folder.name.name, files);
  console.log(currentFolder.value)

  currentPage.value = 1;
};

const navigateBack = () => {
  currentFolder.value = null
  currentPage.value = 1
}

const downloadFile = (file) => {
  const link = document.createElement('a')
  link.href = file.url
  link.download = file.name
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

/* const openCreateFolderModal = () => {
  newFolderName.value = ''
  isCreateFolderModalOpen.value = true
} */

const createFolder = () => {
  if (!newFolderName.value.trim()) {
    alert("Le nom du dossier ne peut pas être vide.");
    return;
  }

  emit('create-folder', { name: newFolderName.value });
  isCreateFolderModalOpen.value = false;
};

const openRenameModal = (item, type) => {
  renameValue.value = item.name
  renameType.value = type
  itemToRename.value = item
  isRenameModalOpen.value = true
}

const renameItem = () => {
  emit('rename-item', {
    item: itemToRename.value,
    newName: renameValue.value,
    type: renameType.value
  })
  isRenameModalOpen.value = false
}

const openUploadModal = () => {
  isUploadModalOpen.value = true
}

const handleUploadComplete = async () => {
  isUploadModalOpen.value = false;
  emit('upload-document');
};

const removeFolder = (folder) => {
  emit('remove-folder', folder)
}

const removeFile = (file) => {
  emit('remove-file', file)
}


const handleModalClose = () => {
  isCreateFolderModalOpen.value = false;
  isRenameModalOpen.value = false;
  isUploadModalOpen.value = false;
};

watch(showOnlyFiles, () => {
  if (showOnlyFiles.value) {
    currentFolder.value = null; // Réinitialiser le dossier actuel lorsque "Afficher uniquement les fichiers" est activé
  }
});
</script>
