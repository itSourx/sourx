<template>
    <div class="centered-container">
        <div class="w-full max-w-md">
            <h1 class="text-2xl my-8">Changer mon mot de passe</h1>
            <form @submit.prevent="handlePasswordChange" class="text-night">
                <div class="relative">
                    <label for="newPassword" class="block text-sm font-medium">Nouveau mot de passe *</label>
                    <div class="relative flex items-center">
                        <span class="absolute inset-y-0 left-0 pl-3 pt-1 flex items-center">
                            <LockKeyhole class="text-gray-light w-5 h-5" />
                        </span>
                        <input v-model="newPassword" type="password" id="newPassword" name="newPassword"
                            class="pl-12 mt-1 p-3 py-5 w-full rounded-md text-xs border border-gray-light" />
                    </div>

                    <label for="confirmPassword" class="block text-sm font-medium mt-4">Confirmer le mot de passe *</label>
                    <div class="relative flex items-center">
                        <span class="absolute inset-y-0 left-0 pl-3 pt-1 flex items-center">
                            <LockKeyhole class="text-gray-light w-5 h-5" />
                        </span>
                        <input v-model="confirmPassword" type="password" id="confirmPassword" name="confirmPassword"
                            class="pl-12 mt-1 p-3 py-5 w-full rounded-md text-xs border border-gray-light" />
                    </div>
                </div>

                <LoaderComponent v-if="isLoading"></LoaderComponent>

                <button type="submit"
                    class="bg-oxford-blue text-white p-3 py-5 mb-4 w-full rounded-md mt-5 text-xs hover:bg-zaffre hover:shadow-lg transform transition duration-200 ease-in-out">
                    Changer le mot de passe
                </button>
            </form>
        </div>
    </div>
</template>


<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import LoaderComponent from '@/components/LoaderComponent.vue';
import { useLoginStore } from '@/stores/AuthentificationStore/AuthStore';

const router = useRouter();
const newPassword = ref('');
const confirmPassword = ref('');
const isLoading = ref(false);
const loginStore = useLoginStore();

const handlePasswordChange = async () => {
    if (!newPassword.value || !confirmPassword.value) {
        toast.error('Les champs de mot de passe ne doivent pas être vides');
        return;
    }

    if (newPassword.value !== confirmPassword.value) {
        toast.error('Les mots de passe ne correspondent pas');
        return;
    }

    isLoading.value = true;
    try {
        await loginStore.firstLoginChange(newPassword.value, router);
    } catch (error) {
        console.error(error);
        toast.error('Échec de la modification du mot de passe');
    } finally {
        isLoading.value = false;
    }
};

</script>


<style scoped>
.centered-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}
</style>
