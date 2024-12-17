<template>
    <div class="forgot-password" v-loading="loading">
        <h1 class="text-2xl font-bold mb-4">Mot de passe oublié ?</h1>
        <p class="text-gray-600 mb-6">Veuillez entrer votre adresse e-mail pour recevoir un code de réinitialisation de
            mot de passe.</p>

        <el-steps :active="activeStep" style="max-width: 600px" align-center>
            <el-step title="Saisir l'email" description="Entrer votre email" />
            <el-step title="Vérifier le code" description="Entrer le code reçu" />
            <el-step title="Nouveau mot de passe" />
        </el-steps>

        <div class="step-content">
            <!-- Étape 1 : Saisie de l'email -->
            <el-form v-if="activeStep === 0" ref="emailFormRef" :model="emailForm" label-width="120px"
                @submit.prevent="submitEmailForm">
                <el-form-item label="Email" :label-position="labelPosition" prop="email" :rules="[
                    { required: true, message: 'Veuillez entrer votre adresse e-mail', trigger: 'blur' },
                    { type: 'email', message: 'Veuillez entrer une adresse e-mail valide', trigger: ['blur', 'change'] },
                ]">
                    <el-input :size="size" v-model="emailForm.email" placeholder="Entrez votre email" />
                </el-form-item>
                <el-button :label-position="labelPosition" class="w-full" :size="size" type="primary"
                    @click="submitEmailForm">Envoyer</el-button>
            </el-form>

            <el-form v-if="activeStep === 1" ref="codeFormRef" :model="codeForm" label-width="120px"
                @submit.prevent="submitCodeForm">
                <el-form-item label="Code de vérification" :label-position="labelPosition"
                    :rules="[{ required: true, message: 'Veuillez entrer le code', trigger: 'blur' }]">
                    <el-input v-model="codeForm.code" :size="size" placeholder="Entrez le code" />
                </el-form-item>
                <el-button :label-position="labelPosition" class="w-full" :size="size" type="primary"
                    @click="submitCodeForm">Vérifier</el-button>
            </el-form>

            <el-form v-if="activeStep === 2" ref="passwordFormRef" :model="passwordForm" :rules="passwordRules"
                label-width="120px" @submit.prevent="submitPasswordForm">
                <el-form-item label="Mot de passe" :label-position="labelPosition" prop="password">
                    <el-input v-model="passwordForm.password" type="password" :size="size"
                        placeholder="Entrez votre nouveau mot de passe" />
                </el-form-item>
                <el-form-item label="Confirmer le mot de passe" :label-position="labelPosition" prop="confirmPassword">
                    <el-input v-model="passwordForm.confirmPassword" type="password" :size="size"
                        placeholder="Confirmez votre mot de passe" />
                </el-form-item>
                <el-button :label-position="labelPosition" class="w-full" :size="size" type="primary"
                    @click="submitPasswordForm">Changer le mot de
                    passe</el-button>
            </el-form>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, reactive } from 'vue'
import type { FormInstance, ComponentSize, FormRules, FormProps } from 'element-plus'
import { useAuthStore } from '@/stores/authStore'
import { ElMessage } from 'element-plus'


const userStore = useAuthStore()

const size = ref<ComponentSize>('large')
const labelPosition = ref<FormProps['labelPosition']>('top')

const activeStep = ref(0)
const loading = ref(false)

const emailForm = reactive({ email: '' })
const emailFormRef = ref<FormInstance>()

const codeForm = reactive({ code: '' })
const codeFormRef = ref<FormInstance>()

const passwordForm = reactive({
    password: '',
    confirmPassword: ''
})
const passwordFormRef = ref<FormInstance>()

const passwordRules = reactive<FormRules>({
    password: [
        { required: true, message: 'Veuillez entrer votre mot de passe', trigger: 'blur' }
    ],
    confirmPassword: [
        { required: true, message: 'Veuillez confirmer votre mot de passe', trigger: 'blur' },
        {
            validator: (rule: any, value: any, callback: any) => {
                if (value !== passwordForm.password) {
                    callback(new Error("Les mots de passe ne correspondent pas"))
                } else {
                    callback()
                }
            }, trigger: 'blur'
        }
    ]
})

const submitEmailForm = async () => {
    loading.value = true
    try {
        await emailFormRef.value?.validate()
        await userStore.checkEmail(emailForm.email)
        activeStep.value = 1
        loading.value = false
    } catch (error) {
        ElMessage.error("Erreur lors de la vérification de l'email")
        loading.value = false
        console.error(error)
    }
}

const submitCodeForm = async () => {
    loading.value = true
    try {
        await userStore.verifyCode(emailForm.email, codeForm.code)
        activeStep.value = 2
        loading.value = false
    } catch (error) {
        ElMessage.error("Erreur lors de la vérification du code")
        loading.value = false
        console.error(error)
    }
}

const submitPasswordForm = async () => {
    loading.value = true
    try {
        await passwordFormRef.value?.validate()
        await userStore.resetPassword(emailForm.email, passwordForm.password)
        ElMessage.success('Mot de passe changé avec succès !')
        activeStep.value = 0
        emailForm.email = ''
        codeForm.code = ''
        passwordForm.password = ''
        passwordForm.confirmPassword = ''

    } catch (error) {
        ElMessage.error("Erreur rencontrée")
        loading.value = false
        console.error(error)
    }
}

</script>

<style scoped>
.forgot-password {
    width: 60%;
    margin: auto;
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 100vh;
}

.step-content {
    margin-top: 20px;
}
</style>
