<template>
  <div class="recent-activity max-w-7xl mx-auto p-6">
    <h2 class="text-2xl mb-6 text-oxford-blue">Activité récente</h2>
    <ul class="relative space-y-8">
      <li v-for="activity in recentActivity" :key="activity.id" class="flex items-start space-x-4 relative">
        <span :class="getIconBgClass(activity.activity_type)"
          class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center">
          <component :is="getActivityIcon(activity.activity_type)" :class="getIconClass(activity.activity_type)"
            class="w-5 h-5" />
        </span>
        <div>
          <p class="text-oxford-blue text-sm font-semibold">
            {{ activity.activity_type }}
          </p>
          <p class="text-gray text-xs">{{ formatDateString(activity.activity_date) }}</p>
          <p class="text-gray-medium text-sm mt-2">
            {{ activity.description }}
          </p>
          <div v-if="activity.link" class="mt-2">
            <a :href="activity.link.url" class="inline-flex items-center text-blue hover:underline">
              <PaperclipIcon class="w-4 h-4 mr-1" />
              {{ activity.link.text }}
            </a>
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useUserStore } from '@/stores/UserStore/UserStore'
import {
  FileText,
  FilePlus,
  Trash2,
  Edit3,
  CheckCircle,
  XCircle,
  PaperclipIcon
} from 'lucide-vue-next'

const userStore = useUserStore()

const recentActivity = ref(userStore.recentActivities)
const loadingActivities = ref(true)

onMounted(async () => {
  await userStore.fetchRecentActivities()
  if (Array.isArray(userStore.recentActivities)) {
    recentActivity.value = userStore.recentActivities.sort(
      (a, b) => new Date(b.activity_date) - new Date(a.activity_date)
    )
  } else {
    recentActivity.value = []
  }
  loadingActivities.value = false
})

const getActivityIcon = (activityType) => {
  switch (activityType) {
    case 'Ajout/Creation':
      return FilePlus
    case 'Suppression':
      return Trash2
    case 'Modification':
      return Edit3
    case 'Acceptation':
      return CheckCircle
    case 'Refus':
      return XCircle
    default:
      return FileText // Icône par défaut si aucun match
  }
}

const getIconClass = (activityType) => {
  switch (activityType) {
    case 'Acceptation':
      return 'text-white'
    case 'Refus':
      return 'text-white'
    default:
      return 'text-white' // Couleur par défaut des icônes
  }
}

const getIconBgClass = (activityType) => {
  switch (activityType) {
    case 'Acceptation':
      return 'bg-green'
    case 'Refus':
      return 'bg-red'
    default:
      return 'bg-oxford-blue' // Couleur de fond par défaut des icônes
  }
}

const formatDateString = (dateString) => {
  const options = {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  }
  return new Date(dateString).toLocaleDateString('fr-FR', options)
}
</script>

<style scoped>
.recent-activity {
  height: 100vh;
  overflow-y: auto;
}

.recent-activity h2 {
  color: var(--oxford-blue);
}

.recent-activity ul {
  list-style: none;
  padding: 0;
  margin: 0;
  position: relative;
}

.recent-activity li span {
  z-index: 2;
}

.recent-activity li {
  align-items: flex-start;
  position: relative;
}
</style>
