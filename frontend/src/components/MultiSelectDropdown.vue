<template>
  <div class="multi-select-dropdown w-full">
    <div class="selected-users flex flex-wrap items-center border border-gray-light rounded-md p-2">
      <div v-for="user in selected" :key="user.id"
        class="selected-user flex items-center m-1 p-1 bg-gray-medium rounded-md">
        <img :src="user.photo" alt="User Photo" class="w-6 h-6 rounded-full mr-2" v-if="user.photo" />
        <span>{{ user.name }} </span>
        <button @click="removeUser(user.id)" class="ml-2 text-red-600">x</button>
      </div>
      <input type="text" v-model="searchQuery" placeholder="Rechercher un nom" class="border-none focus:outline-none flex-grow" />
    </div>
    <div class="dropdown mt-2 border border-gray-light rounded-md bg-white w-full">
      <div class="dropdown-item flex items-center p-2" @click.stop="toggleSelectAll">
        <span>Tout sélectionner</span>
        <input type="checkbox" class="ml-auto" :checked="isAllSelected" />
      </div>
      <div v-for="item in filteredItems" :key="item.id" class="dropdown-item flex items-center p-2 hover:bg-gray-100 cursor-pointer" @click.stop="toggleSelection(item)">
        <img :src="item.photo" alt="User Photo" class="w-6 h-6 rounded-full mr-2" v-if="item.photo" />
        <span>{{ item.name }}</span>
        <input type="checkbox" class="ml-auto" :checked="isSelected(item)" />
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  items: {
    type: Array,
    required: true
  },
  modelValue: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const searchQuery = ref('')
const selected = ref([...props.modelValue])
const filteredItems = computed(() => {
  return props.items.filter((item) =>
    item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

watch(
  () => props.modelValue,
  (newVal) => {
    selected.value = [...newVal]
  },
  { immediate: true }
)

watch(
  () => props.items,
  (newVal) => {
    console.log('MultiSelectDropdown items updated:', newVal)
    filteredItems.value = newVal
  },
  { immediate: true }
)

const isSelected = (item) => {
  return selected.value.find((selectedItem) => selectedItem.id === item.id)
}

const toggleSelection = (item) => {
  if (isSelected(item)) {
    selected.value = selected.value.filter((selectedItem) => selectedItem.id !== item.id)
  } else {
    selected.value.push(item)
  }
  emit('update:modelValue', selected.value)
}

const removeUser = (id) => {
  selected.value = selected.value.filter((user) => user.id !== id)
  emit('update:modelValue', selected.value)
}

const isAllSelected = computed(() => {
  return filteredItems.value.length > 0 && filteredItems.value.every((item) => isSelected(item))
})

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selected.value = selected.value.filter(
      (selectedItem) => !filteredItems.value.some((item) => item.id === selectedItem.id)
    )
  } else {
    const newSelected = filteredItems.value.filter((item) => !isSelected(item))
    selected.value = [...selected.value, ...newSelected]
  }
  emit('update:modelValue', selected.value)
}
</script>

<style scoped>
.selected-users {
  display: flex;
  flex-wrap: wrap;
}

.selected-user {
  background-color: #f0f0f0;
  border-radius: 5px;
  padding: 2px 6px;
  display: flex;
  align-items: center;
}

.dropdown {
  max-height: 200px;
  overflow-y: auto;
}
.dropdown {
  max-height: 200px;
  transition: max-height 0.3s ease;
}

.dropdown.open {
  max-height: 200px;
  transition: max-height 0.3s ease;
}

.dropdown-item {
  cursor: pointer;
  transition: background-color 0.2s ease;
  display: flex;
  align-items: center;
  padding: 5px 10px;
}

.dropdown-item:hover {
  background-color: #e5e7eb; 
}

</style>
