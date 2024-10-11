<template>
  <div class="dropdown-container w-full border gray-light-medium rounded-md px-7 py-4 focus:outline-none">
    <div class="dropdown-button noselect flex items-center justify-between" @click="toggleDropdown">
      <div class="dropdown-label">{{ title }} ({{ numChecked }}) sélectonnée (s)</div>
      <span class="ml-2" :class="{ 'rotate-90': isDropdownOpen }">&#9658;</span>
    </div>

    <div class="dropdown-list" :style="{ display: isDropdownOpen ? 'block' : 'none' }">
      <div class="relative">
        <input type="search" placeholder="Recherche des employés..."
          class="w-full text-gray-medium border gray-light-light rounded-lg py-2 px-4 pl-10 focus:outline-none dropdown-search"
          @input="filterStates" />
        <span class="absolute inset-y-0 left-3 flex items-center">
          <SearchIcon class="w-4 h-4 text-gray-medium" />
        </span>
      </div>
      <ul class="overflow-auto mt-2">
        <li v-for="state in filteredStates" :key="state.name" class="py-1">
          <input type="checkbox" :id="state.name" :name="state.name" :value="state.id" v-model="checkedStates"
            @change="updateQuantity()" class="mr-2" />
          <label :for="state.name" class="select-none">{{ state.name }}</label>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useSelectionRecipient } from '@/stores/store'

const props = defineProps({
  states: {
    type: Array,
    required: true
  },
  title: {
    type: String,
    required: true
  }
})

const isDropdownOpen = ref(false)
const checkedStates = ref([])
const numChecked = ref(0)

const filteredStates = ref(props.states)

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value
}

const filterStates = (event) => {
  const search = event.target.value.toLowerCase()
  filteredStates.value = props.states.filter((state) => state.name.toLowerCase().includes(search))
}

const updateQuantity = () => {
  numChecked.value = checkedStates.value.length
  useSelectionRecipient().setSelectedIndividuals(JSON.parse(JSON.stringify(checkedStates.value)))
}
</script>

<style scoped>
.noselect {
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

.dropdown-container {
  margin: 20px auto 0;
  font-size: 14px;
  overflow: auto;
}

.rotate-90 {
  transform: rotate(90deg);
}

.fa-filter {
  float: right;
}

.dropdown-list ul {
  max-height: 10rem !important;
}

.dropdown-search {
  width: calc(100% - 30px);
  /* Prend 100% moins la largeur de l'icône */
  padding-left: 10px;
  /* Espacement à gauche du texte */
  /* Bordure grise */
  border-radius: 4px;
  /* Coins arrondis */
  height: 34px;
  /* Hauteur */
}

.search-icon {
  position: absolute;
  top: 50%;
  right: 10px;
  transform: translateY(-50%);
  color: #888;
  /* Couleur de l'icône */
}

ul {
  margin: 10px 0;
  max-height: 200px;
  overflow-y: auto;
}

ul input[type='checkbox'] {
  position: relative;
  top: 2px;
}
</style>
