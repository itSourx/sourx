<template>
    <div class="max-w-7xl mx-auto mt-4">
        <div class="flex justify-between items-center my-8">
            <h3 class="text-xl font-bold">Mon profil</h3>
        </div>
        <el-form :model="userInfo" :rules="rules" ref="formRef" label-width="120px">
            <el-form-item label="Prénom" label-position="top" prop="first_name">
                <el-input v-model="userInfo.first_name" size="large" placeholder="Entrez votre prénom" />
            </el-form-item>
            <el-form-item label="Nom" label-position="top" prop="last_name">
                <el-input v-model="userInfo.last_name" size="large" placeholder="Entrez votre nom" />
            </el-form-item>
            <el-form-item label="Email" label-position="top" prop="email">
                <el-input v-model="userInfo.email" disabled size="large" placeholder="Entrez votre email" />
            </el-form-item>
            <el-form-item label="Numéro de téléphone" label-position="top" prop="phone_number">
                <el-input v-model="userInfo.phone_number" size="large" placeholder="Entrez votre numéro de téléphone" />
            </el-form-item>
            <el-form-item label="Role" label-position="top">
                <el-input v-model="userInfo.role" disabled size="large" placeholder="Role" />
            </el-form-item>
            <el-form-item label="Adresse" label-position="top" prop="address">
                <el-input v-model="userInfo.address" size="large" placeholder="Entrez votre adresse" />
            </el-form-item>
            <el-form-item label-position="top">
                <el-button type="primary" class="w-full" size="large" @click="submitProfileUpdate">Mettre à
                    jour</el-button>
            </el-form-item>
        </el-form>
    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import type { UploadInstance, FormInstance } from 'element-plus';
import { ElMessage } from 'element-plus';

const authStore = useAuthStore();

// Récupération des informations de l'utilisateur
const userInfo = ref({ ...authStore.user });

// Définition des règles de validation
const rules = ref({
    first_name: [
        { required: true, message: 'Veuillez entrer votre prénom', trigger: 'blur' },
        { min: 2, message: 'Le prénom doit comporter au moins 2 caractères', trigger: 'blur' }
    ],
    last_name: [
        { required: true, message: 'Veuillez entrer votre nom', trigger: 'blur' },
        { min: 2, message: 'Le nom doit comporter au moins 2 caractères', trigger: 'blur' }
    ],
    email: [
        { required: true, message: 'Veuillez entrer votre adresse email', trigger: 'blur' },
        { type: 'email', message: 'Veuillez entrer une adresse email valide', trigger: ['blur', 'change'] }
    ],
    phone_number: [
        { required: true, message: 'Veuillez entrer votre numéro de téléphone', trigger: 'blur' },
        {
            pattern: /^\+?[0-9]{10,15}$/,
            message: 'Le numéro doit comporter entre 10 et 15 chiffres, avec un "+" optionnel au début',
            trigger: 'blur'
        }
    ],
    address: [
        { required: true, message: 'Veuillez entrer votre adresse', trigger: 'blur' }
    ]
});

// Référence au formulaire
const formRef = ref<FormInstance>();

// Fonction pour soumettre la mise à jour du profil
const submitProfileUpdate = async () => {
    if (!formRef.value) return;
    formRef.value.validate(async (valid) => {
        if (valid) {
            try {
                const updatedData = {
                    first_name: userInfo.value.first_name,
                    last_name: userInfo.value.last_name,
                    phone_number: userInfo.value.phone_number,
                    address: userInfo.value.address,
                };
                await authStore.updateUser(updatedData);
                ElMessage({
                    message: 'Modifications enregistrées. Elles seront prises en compte à votre prochaine connexion.',
                    type: 'success',
                    duration: 5000,
                });
            } catch (error) {
                ElMessage.error('Erreur lors de la mise à jour du profil');
            }
        } else {
            ElMessage.error('Veuillez corriger les erreurs dans le formulaire');
        }
    });
};

// Gestion de l'upload de la photo de profil
const uploadRef = ref<UploadInstance>();

const handleUploadChange = () => {
    uploadRef.value!.submit();
};
</script>