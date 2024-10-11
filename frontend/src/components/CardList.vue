<template>
  <div @click="viewRequest"
    class="card flex flex-col lg:flex-row justify-between items-center p-4 cursor-pointer transition duration-300 ease-in-out hover:bg-gray-100">
    <div class="flex-grow lg:w-1/3 lg:mr-4">
      <h4 class="text-sm">{{ title }}</h4>
      <small class="text-xs text-gray-medium">{{ formatDateString(dateRequest) }}</small>
    </div>
    <div v-if="documentsCount > 0" class="flex items-center justify-center lg:w-1/6 mt-4 lg:mt-0">
      <PaperclipIcon class="w-5 h-5 mr-2 text-gray-medium" />
      <span class="text-xs">{{ documentsCount }} </span>
    </div>
    <div class="flex items-center justify-center lg:w-1/6 mt-4 lg:mt-0">
      <StatusTag :status="status" />
    </div>
    <div v-if="status === 'Soumis'" class="flex items-center justify-center lg:w-1/6 mt-4 lg:mt-0">
      <Edit3 @click.stop="openEditModal(demande)"
        class="w-5 h-5 text-gray-medium cursor-pointer hover:text-oxford-blue" />
    </div>
  </div>
</template>

<script setup>
import StatusTag from './StatusTag.vue'
import { formatDateString } from '@/utils'
import { PaperclipIcon } from 'lucide-vue-next'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  dateRequest: {
    type: String,
    required: true
  },
  status: {
    type: String,
    required: true
  },
  dateResponse: {
    type: String,
    required: true
  },
  demande: {
    type: Object,
    required: false
  },
  documentsCount: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['view-request', 'edit-request']);

function viewRequest() {
  emit('view-request', props.demande)
}

const openEditModal = (demande) => {
  emit('edit-request', demande);
};

const validStatusValues = ['Approuvée', 'Refusée', 'En cours', 'Soumis']

if (!validStatusValues.includes(props.status)) {
  console.error(
    `Invalid status value "${props.status}". The status must be one of: ${validStatusValues.join(', ')}`
  )
}

</script>

<style scoped>
.card {
  background-color: #ffffff;
  border-bottom: 1px solid #ccc;
}

.card:hover {
  background-color: #f7f7f7;
  /* Couleur d'arrière-plan légèrement plus foncée au survol */
}
</style>
