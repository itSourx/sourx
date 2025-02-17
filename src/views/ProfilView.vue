<template>
    <div class="max-w-7xl mx-auto mt-4">
        <div class="bg-blue-500 text-white p-6 mb-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold">Bienvenue sur votre profil, {{ userInfo.first_name }}!</h2>
            <p class="mt-2">Gérez vos informations personnelles et vos préférences ici.</p>
        </div>

        <el-form :model="userInfo" :rules="rules" ref="formRef" label-width="120px" class="flex flex-wrap">
            <div class="w-full md:w-1/2 p-4">
                <el-form-item label="Prénom" prop="first_name" label-position="top">
                    <el-input v-model="userInfo.first_name" size="large" placeholder="Entrez votre prénom">
                        <template #prefix>
                            <el-icon class="el-input__icon">
                                <User />
                            </el-icon>
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item label="Nom" prop="last_name" label-position="top">
                    <el-input v-model="userInfo.last_name" size="large" placeholder="Entrez votre nom">
                        <template #prefix>
                            <el-icon class="el-input__icon">
                                <User />
                            </el-icon>
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item label="Email" prop="email" label-position="top">
                    <el-input v-model="userInfo.email" disabled size="large" placeholder="Entrez votre email">
                        <template #prefix>
                            <el-icon class="el-input__icon">
                                <Message />
                            </el-icon>
                        </template>
                    </el-input>
                </el-form-item>
            </div>
            <div class="w-full md:w-1/2 p-4">
                <el-form-item label="Numéro de téléphone" prop="phone_number" label-position="top">
                    <el-input v-model="userInfo.phone_number" size="large"
                        placeholder="Entrez votre numéro de téléphone">
                        <template #prefix>
                            <el-icon class="el-input__icon">
                                <Phone />
                            </el-icon>
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item label="Role" label-position="top">
                    <el-input v-model="userInfo.role" disabled size="large" placeholder="Role">
                        <template #prefix>
                            <el-icon class="el-input__icon">
                                <OfficeBuilding />
                            </el-icon>
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item label="Adresse" prop="address" label-position="top">
                    <el-input v-model="userInfo.address" size="large" placeholder="Entrez votre adresse">
                        <template #prefix>
                            <el-icon class="el-input__icon">
                                <Location />
                            </el-icon>
                        </template>
                    </el-input>
                </el-form-item>
            </div>
            <div class="w-full p-4">
                <!-- <el-form-item label="Langue" prop="language" label-position="top">
                    <el-select v-model="userInfo.language" size="large" placeholder="Sélectionnez votre langue">
                        <el-option label="English (US)" value="en" />
                        <el-option label="Français" value="fr" />
                    </el-select>
                </el-form-item> -->
                <el-button type="primary" class="w-full" size="large" @click="submitProfileUpdate">
                    Mettre à jour
                </el-button>
            </div>
        </el-form>
    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import type { FormInstance } from 'element-plus';
import { ElMessage } from 'element-plus';
import { User, Message, Phone, OfficeBuilding, Location } from '@element-plus/icons-vue';

const authStore = useAuthStore();

// Récupération des informations de l'utilisateur
const userInfo = ref({ ...authStore.user, language: 'en' });

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
                    language: userInfo.value.language,
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
</script>