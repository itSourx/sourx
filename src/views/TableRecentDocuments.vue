<template>
  <div class="recent-documents-table w-full overflow-x-auto my-3" v-loading="loading">
    <!-- Barre de recherche -->
    <div class="mb-4">
      <el-input v-model="search" size="large" placeholder="Rechercher par nom de document" clearable
        :prefix-icon="Search" class="w-full" />
    </div>

    <el-table :data="paginatedTableData" style="width: 100%" empty-text="Pas de documents" @row-click="onRowClicked">

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
          {{ `${scope.row['first_name (from uploaded_by)']} ${scope.row['last_name (from uploaded_by)']}` }}
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
            {{ `${scope.row['first_name (from user_receiver)'][0]} ${scope.row['last_name (from user_receiver)'][0]}` }}

            <!-- Si plus d'un receiver, affichez "..." avec Popover pour afficher les autres -->
            <el-popover v-if="scope.row['first_name (from user_receiver)']?.length > 1" placement="top" width="200"
              trigger="hover">
              <ul>
                <li v-for="(firstName, index) in scope.row['first_name (from user_receiver)'].slice(1)" :key="index">
                  <UserAvatar
                    :userName="`${scope.row['first_name (from user_receiver)'][0]} ${scope.row['last_name (from user_receiver)'][0]}`"
                    class="mx-1" />
                  {{ `${firstName} ${scope.row['last_name (from user_receiver)'][index + 1]}` }}
                </li>
              </ul>
              <template #reference>
                <span class="text-blue-600 cursor-pointer ml-1">...</span>
              </template>
            </el-popover>
          </template>
          <!-- Affichez un message ou un espace vide si aucun nom n'est disponible -->
          <template v-else>
            <span>Supérieur hiérarchique</span>
          </template>
        </template>
      </el-table-column>

      <el-table-column label="" width="70">
        <template #default="scope">
          <el-button type="danger" @click="confirmRemoveFile(scope.row.id)">
            <el-icon>
              <Delete />
            </el-icon>
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <div v-if="totalDocuments.value > 0" class="w-full flex justify-end mt-4">
  <el-pagination :total="totalDocuments.value" :page-size="pageSize.value" v-model:current-page="currentPage.value"
    @current-change="handlePageChange" layout="total, prev, pager, next" />
</div>
  </div>
</template>

<script lang="ts" setup>
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

const loadDocuments = async () => {
  console.log("---000--")

  loading.value = true;
  console.log("---0--")
  documentList.value = [];
  await documentStore.getAllDocuments(currentPage.value, true);
  console.log(documentStore.documents)
  documentList.value = documentStore.documents;
  console.log("---1---")
  console.log(documentList.value)
  totalDocuments.value = documentStore.totalDocuments;
  loading.value = false;
};

watch(() => props.reloadDocuments, async () => {
  await loadDocuments();
});


const sortedTableData = computed(() => {
  console.log("---2---")
  console.log(documentList.value)
  return documentList.value.slice().sort((a, b) => {
    return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
  });
});

const filteredTableData = computed(() => {
  return sortedTableData.value.filter((document) =>
    document.name.toLowerCase().includes(search.value.toLowerCase())
  );
});

const paginatedTableData = computed(() => {
  if (!documentList.value || documentList.value.length === 0) {
    return [];
  }
  return filteredTableData.value.slice(0, 10);
});

// Fonction pour récupérer les documents de la page actuelle
const handlePageChange = async (page: number) => {
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

function onRowClicked(document) {
  emit('row-clicked', document);
}

watch(documentList, (newDocuments) => {
  console.log('Documents mis à jour:', newDocuments);
});
</script>

<style scoped>
.flex {
  display: flex;
  align-items: center;
}
</style>
