<template>
  <form @submit.prevent="handleSubmitProfilEdition" class="px-2 py-12">
    <div class="flex justify-between w-full mb-4 space-x-4">
      <div class="w-1/2">
        <label class="block mb-1">Nom</label>
        <input type="text" v-model="newActiveUserValue.nom" class="input-border w-full p-2" />
      </div>
      <div class="w-1/2">
        <label class="block mb-1">Prenom</label>
        <input type="text" v-model="newActiveUserValue.prenom" class="input-border w-full p-2" />
      </div>
    </div>
    <div class="flex justify-between w-full mb-4 space-x-4">
      <div class="w-1/2">
        <label class="block mb-1">Email</label>
        <input type="email" v-model="newActiveUserValue.email" class="input-border w-full p-2" />
      </div>
      <div class="w-1/2">
        <label class="block mb-1">Telephone</label>
        <vue-tel-input v-model="newActiveUserValue.telephone" :inputoptions="{ showDialCode: true }"></vue-tel-input>
      </div>
    </div>
    <div class="flex justify-end">
      <button class="bg-blue text-white py-2 px-5 rounded-md shadow cursor-pointer" type="submit">
        Modifier
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  user: Object
})

const newActiveUserValue = ref({ ...props.user })
const originalUserValue = ref({ ...props.user })

const handleSubmitProfilEdition = async () => {
  console.log('dd')
  try {
    const response = await axios.post('/user/update', {
      id: newActiveUserValue.value.id,
      nom: newActiveUserValue.value.nom,
      prenom: newActiveUserValue.value.prenom,
      email: newActiveUserValue.value.email,
      telephone: newActiveUserValue.value.telephone
    })

    if (response.status === 200) {
      alert('Profil mis à jour avec succès')
      originalUserValue.value = { ...newActiveUserValue.value }
    } else {
      alert('Erreur lors de la mise à jour du profil')
    }
  } catch (error) {
    console.error('Error updating profile:', error)
    alert('Erreur lors de la mise à jour du profil')
  }
}

watch(
  () => props.user,
  (newUser) => {
    newActiveUserValue.value = { ...newUser }
    originalUserValue.value = { ...newUser }
  }
)
</script>

<style>
.input-border {
  border: 1px solid #gray-medium;
  border-radius: 4px;
}

.input-border:focus {
  border-color: orange;
  box-shadow: 0 0 5px rgba(255, 165, 0, 0.5);
  outline: none;
}

.vue-tel-input input {
  border: 1px solid #gray-medium !important;
  border-radius: 4px !important;
  padding: 8px !important;
  width: 100% !important;
}

.vue-tel-input input:focus {
  border-color: orange !important;
  box-shadow: 0 0 5px rgba(255, 165, 0, 0.5) !important;
  outline: none !important;
}
</style>
