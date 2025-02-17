<template>
    <div class="login">
        <h1 class="text-2xl font-bold mb-4">Connexion à votre compte</h1>
        <p class="text-gray-600 mb-6">Veuillez entrer votre email et votre mot de passe pour accéder à votre
            espace personnel.</p>

        <el-form ref="formRef" :model="loginForm" label-width="120px" class="login-form"
            @submit.prevent=" submitForm(formRef)">
            <el-form-item label="Email" :label-position="labelPosition" prop="email" :rules="[
                { required: true, message: 'Veuillez entrer votre adresse e-mail', trigger: 'blur' },
                { type: 'email', message: 'Veuillez entrer une adresse e-mail valide', trigger: ['blur'] },
            ]">
                <template #label>
                    <span style="font-weight: bold;">Email</span>
                </template>
                <el-input :size="size" v-model="loginForm.email" placeholder="Entrez votre email" :autocomplete="'on'"
                    prefix-icon="Message" :input-style="{ fontWeight: 'bold' }" />
            </el-form-item>
            <el-form-item label="Mot de passe" :label-position="labelPosition" prop="password" :rules="[
                { required: true, message: 'Veuillez entrer votre mot de passe', trigger: 'blur' },
            ]">
                <template #label>
                    <span style="font-weight: bold;">Mot de passe</span>
                </template>
                <el-input :size="size" v-model="loginForm.password" type="password"
                    placeholder="Entrez votre mot de passe" show-password suffix-icon="el-icon-view"
                    prefix-icon="Unlock" />
            </el-form-item>

            <el-form-item :label-position="labelPosition">
                <el-button type="primary" @click="submitForm(formRef)" class="w-full" :size="size"
                    native-type="submit">Connexion</el-button>
            </el-form-item>

            <el-form-item class="text-right" :label-position="labelPosition">
                <router-link to="/auth/forgot-password" class="text-blue-500 hover:underline">Mot de passe
                    oublié ?</router-link>
            </el-form-item>
        </el-form>
    </div>

</template>

<script lang="ts" setup>
import { ref } from 'vue';
import type { FormInstance, ComponentSize, FormProps } from 'element-plus';
import { ElMessage, ElLoading } from 'element-plus';
import { useAuthStore } from '@/stores/authStore';

const isSubmitting = ref(false);
const size = ref<ComponentSize>('large');
const labelPosition = ref<FormProps['labelPosition']>('top');

const formRef = ref<FormInstance>();
const loginForm = ref({
    email: '',
    password: '',
});

const authStore = useAuthStore();

const submitForm = async (formEl: FormInstance | undefined) => {
    if (!formEl || isSubmitting.value) return; // Empêche une double soumission
    isSubmitting.value = true;

    let loadingInstance;
    try {
        const valid = await formEl.validate();
        if (valid) {
            // Affiche le chargement
            loadingInstance = ElLoading.service({
                text: 'Connexion...',
            });

            console.log('Formulaire soumis!', loginForm.value);
            await authStore.login(loginForm.value.email, loginForm.value.password);
            ElMessage.success('Connexion réussie !');
        } else {
            console.log('Erreur lors de la validation du formulaire!');
        }
    } catch (err) {
        console.error('Erreur lors de la validation ou de la soumission :', err);
        ElMessage.error('Erreur lors de la connexion.');
    } finally {
        // Arrête le chargement
        if (loadingInstance) {
            loadingInstance.close();
        }
        isSubmitting.value = false; // Libère le verrou
    }
};
</script>


<style scoped>
.login {
    max-width: 400px;
    width: 100%;
    margin: auto;
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 100vh;
}
</style>
