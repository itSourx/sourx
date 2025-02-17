<template>
  <div class="recent-documents-table w-full overflow-x-auto my-3" v-loading="loading">

    <!-- Barre de recherche -->
    <div class="mb-4 flex flex-col md:flex-row justify-between items-center">
      <el-input v-model="search" size="large" placeholder="Rechercher par nom de document" clearable
        :prefix-icon="Search" class="w-full md:w-1/4 mb-4 md:mb-0 md:mr-4" />
      <div class="flex flex-col md:flex-row items-center w-full md:w-auto">
        <el-select v-model="fileTypeFilter" placeholder="Filtrer par type de fichier"
          class="w-full md:w-auto mb-4 md:mb-0 md:mr-4" size="large" clearable>
          <el-option v-for="option in fileTypeOptions" :key="option.value" :label="option.label" :value="option.value">
            <div class="flex items-center">
              <img :src="getFileTypeIcon(option.value)" alt="" class="w-6 h-6 mr-2">
              <span>{{ option.label }}</span>
            </div>
          </el-option>
        </el-select>
        <el-date-picker v-model="dateRange" size="large" type="daterange" range-separator="à"
          start-placeholder="Date de début" end-placeholder="Date de fin" class="w-full md:w-auto" clearable />
      </div>
    </div>


    <el-table :data="paginatedTableData" style="width: 100%" empty-text="Pas de documents">

      <el-table-column label="Nom" sortable>
        <template #default="scope">
          <div class="flex items-center">
            <DocumentIcon :fileName="scope.row.name" />
            <a :href="scope.row.url" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">
              {{ scope.row.name }}
            </a>
          </div>
        </template>
      </el-table-column>

      <el-table-column prop="folderName" label="Dossier" sortable>
        <template #default="scope">
          <div class="flex items-center">
            <span> {{ scope.row['name (from folder)'][0] }} </span>
          </div>
        </template>
      </el-table-column>
      <el-table-column label="Envoyé par">
        <template #default="scope">
          <UserAvatar
            :userName="`${scope.row['first_name (from uploaded_by)']} ${scope.row['last_name (from uploaded_by)']}`"
            class="mx-1" />
            <small>{{ `${scope.row['first_name (from uploaded_by)']} ${scope.row['last_name (from uploaded_by)']}` }}</small>
          
        </template>
      </el-table-column>

      <el-table-column prop="size" label="Taille" sortable>
        <template #default="scope">
          <div class="flex items-center">
            <span> {{ formatSize(scope.row.size) }} </span>
          </div>
        </template>
      </el-table-column>
      <el-table-column prop="created_at" label="Date de Création" sortable>
        <template #default="scope">
          <div class="flex items-center">
            <span> {{ formatDate(scope.row.created_at) }} </span>
          </div>
        </template>
      </el-table-column>
      <!-- Mise à jour de la date de création -->
      <el-table-column prop="user_receiver" label="Partagé avec">
        <template #default="scope">
          <!-- Vérifiez si user_receiver existe et a au moins un nom -->
          <template
            v-if="scope.row['first_name (from user_receiver)'] && scope.row['first_name (from user_receiver)'].length > 0">
            <UserAvatar
              :userName="`${scope.row['first_name (from user_receiver)'][0]} ${scope.row['last_name (from user_receiver)'][0]}`"
              class="mx-1" />
              <small>{{ `${scope.row['first_name (from user_receiver)'][0]} ${scope.row['last_name (from user_receiver)'][0]}` }}
              </small>
            
            <!-- Si plus d'un receiver, affichez "..." avec Popover pour afficher les autres -->
            <el-popover v-if="scope.row['first_name (from user_receiver)']?.length > 1" placement="top" width="200"
              trigger="hover">
              <ul>
                <li v-for="(firstName, index) in scope.row['first_name (from user_receiver)'].slice(1)" :key="index">
                  <UserAvatar
                    :userName="`${scope.row['first_name (from user_receiver)'][0]} ${scope.row['last_name (from user_receiver)'][0]}`"
                    class="mx-1" />
                  <small>{{ `${firstName} ${scope.row['last_name (from user_receiver)'][index + 1]}` }}</small>
                </li>
              </ul>
              <template #reference>
                <span class="text-blue-600 cursor-pointer ml-1">...</span>
              </template>
            </el-popover>
          </template>
          <!-- Affichez un message ou un espace vide si aucun nom n'est disponible -->
          <template v-else>
            <span></span>
          </template>
        </template>
      </el-table-column>

      <el-table-column label="" width="130">
        <template #default="scope">
          <el-button type="primary" icon="View" @click="emit('row-clicked', scope.row)"></el-button>
          <el-button type="danger" icon="Delete" @click="confirmRemoveFile(scope.row.id)"></el-button>
        </template>
      </el-table-column>
    </el-table>

    <div v-if="totalDocuments > 0" class="w-full flex justify-end mt-4">
      <el-pagination :total="totalDocuments" :page-size="pageSize" v-model:current-page="currentPage"
        @current-change="handlePageChange" layout="total, prev, pager, next" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import DocumentIcon from '@/components/DocumentIcon.vue';
import UserAvatar from '@/components/UserAvatar.vue';
import { Search } from '@element-plus/icons-vue';
import { useDocumentStore } from '@/stores/documentStore';
import { ElMessageBox } from 'element-plus';

const emit = defineEmits(['row-clicked']);

const documentStore = useDocumentStore();
const documentList = ref([]);
const search = ref('');
const fileTypeFilter = ref('');
const dateRange = ref([]);
const loading = ref(false)
const currentPage = ref(1);
const pageSize = ref(10);
const totalDocuments = ref(1);

const props = defineProps({
  reloadDocuments: {
    type: Number,
    required: true
  }
});

const fileTypeOptions = [
  { value: 'code', label: 'Code', extensions: ['py', 'js', 'html', 'css', 'json', 'ts', 'java', 'cpp', 'c', 'php', 'rb', 'cs', 'go', 'sh', 'md'], icon: new URL('@/assets/DocumentsIcons/code.png', import.meta.url).href }
];


const getFileTypeIcon = (fileType) => {
  const option = fileTypeOptions.find(option => option.value === fileType);
  return option ? option.icon : '';
};

/* const loadDocuments = async () => {
  loading.value = true;
  documentList.value = [];
  await documentStore.getAllDocuments(currentPage.value, true);
  console.log(documentList.value)
  totalDocuments.value = documentStore.totalDocuments;
  loading.value = false;
}; */

watch(() => props.reloadDocuments, async () => {
  loading.value = true
  await documentStore.getAllDocuments(currentPage.value);
  loading.value = false
  console.log(documentStore)
  documentList.value = documentStore.documents;
  totalDocuments.value = documentStore.totalDocuments
  console.log(totalDocuments.value)

});


const sortedTableData = computed(() => {
  return documentList.value.slice().sort((a, b) => {
    return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
  });
});

const filteredTableData = computed(() => {
  console.log("-----------")
  console.log(sortedTableData)
  console.log("-----------")

  return sortedTableData.value.filter((document) =>
    document.name.toLowerCase().includes(search.value.toLowerCase()) &&
    (fileTypeFilter.value ? fileTypeOptions.find(option => option.value === fileTypeFilter.value)?.extensions.includes(document.name.split('.').pop()?.toLowerCase()) : true) &&
    (dateRange.value.length ? new Date(document.created_at) >= dateRange.value[0] && new Date(document.created_at) <= dateRange.value[1] : true)
  );
});

const paginatedTableData = computed(() => {
  if (!documentList.value || documentList.value.length === 0) {
    return [];
  }
  return filteredTableData.value.slice(0, 10);
});

// Fonction pour récupérer les documents de la page actuelle
const handlePageChange = async (page) => {
  documentList.value = []
  currentPage.value = page;
  loading.value = true;
  await documentStore.getAllDocuments(page, true);
  documentList.value = documentStore.documents;
  totalDocuments.value = documentStore.totalDocuments;
  loading.value = false;
};

function formatSize(sizeInKo) {
  if (sizeInKo >= 1024) {
    const sizeInMo = sizeInKo / 1024;
    return `${sizeInMo.toFixed(2)} Mo`;
  } else {
    return `${sizeInKo.toFixed(2)} Ko`;
  }
}

function formatDate(date) {
  const options = {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  };
  return new Date(date).toLocaleDateString('fr-FR', options).replace(',', ' à');
}

function confirmRemoveFile(fileId) {
  ElMessageBox.confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?', 'Confirmation', {
    confirmButtonText: 'Oui',
    cancelButtonText: 'Annuler',
    type: 'warning'
  }).then(() => {
    removeFile(fileId);
  }).catch(() => {
  });
}

async function removeFile(fileId) {
  loading.value = true;
  await documentStore.removeFile(fileId);
  documentList.value = documentStore.documents;
  loading.value = false;
}

onMounted(async () => {
  loading.value = true
  await documentStore.getAllDocuments(currentPage.value);
  loading.value = false
  documentList.value = documentStore.documents;
  totalDocuments.value = documentStore.totalDocuments
  console.log(documentStore.documents);
});



watch(documentList, (newDocuments) => {
  console.log('Documents mis à jour:', newDocuments);
});
</script>

