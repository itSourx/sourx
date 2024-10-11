<template>
    <div class="excel-import">
        <input type="file" @change="handleFileUpload" accept=".xlsx, .xls" class="mb-4" />
        <button v-if="users.length > 0" @click="uploadUsers" class="bg-oxford-blue text-white py-2 px-4 rounded-md">
            Importer Utilisateurs
        </button>
        <LoaderComponent v-if="isLoading"></LoaderComponent>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import * as XLSX from 'xlsx'
import axios from 'axios'
import { toast } from 'vue3-toastify'
import LoaderComponent from '@/components/LoaderComponent.vue'

const isLoading = ref(false)
const users = ref([])

const handleFileUpload = (event) => {
    const file = event.target.files[0]
    const reader = new FileReader()

    reader.onload = (e) => {
        const data = new Uint8Array(e.target.result)
        const workbook = XLSX.read(data, { type: 'array' })
        const sheetName = workbook.SheetNames[0]
        const worksheet = workbook.Sheets[sheetName]
        const jsonData = XLSX.utils.sheet_to_json(worksheet)

        // Convertir les données Excel en format compatible
        users.value = jsonData.map(row => ({
            nom: row.Nom || '',
            prenom: row.Prenom || '',
            email: row.Email || '',
            telephone: row.Telephone || '',
            role: row.Role || '',
            poste: row.Poste || '',
            Equipe: (row.Equipe || '').split(',').map(equipe => equipe.trim())
        }))

        console.log('Données importées:', users.value)
    }

    reader.readAsArrayBuffer(file)
}

const uploadUsers = async () => {
    if (users.value.length === 0) {
        toast.error('Aucun utilisateur à importer.')
        return
    }

    isLoading.value = true

    try {
        const token = localStorage.getItem('jwt_token')
        const response = await axios.post('http://localhost:8000/api/v1/bulkCreateUsers', { users: users.value }, {
            headers: {
                Authorization: `Bearer ${token}`
            }
        })

        if (response.status === 200) {
            toast.success('Tous les utilisateurs ont été importés avec succès.')
        } else {
            toast.error('Erreur lors de l\'importation des utilisateurs.')
        }
    } catch (error) {
        console.error('Erreur lors de l\'importation des utilisateurs:', error)
        toast.error('Erreur lors de l\'importation des utilisateurs.')
    } finally {
        isLoading.value = false
    }
}
</script>

<style scoped>
.excel-import {
    display: flex;
    flex-direction: column;
    align-items: start;
}
</style>