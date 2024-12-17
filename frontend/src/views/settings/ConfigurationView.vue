<template>
    <div class="max-w-7xl mx-auto mt-4">
        <div class="flex justify-between items-center my-8">
            <h3 class="text-xl font-bold">Configuration de l'Entreprise</h3>
        </div>
        <el-form :model="companyInfo" ref="formRef" label-width="120px"> <!-- :rules="rules" -->
            <!-- Logo -->
            <el-form-item label="Logo de l'entreprise" label-position="top" class="w-full mb-4">
                <el-image style="width: 200px; height: 200px" :src="companyInfo.fields?.logo" fit="contain"
                    class="rounded-sm mr-8" />
                <el-upload v-model:file-list="uploadLogoRef" class="upload-demo" :auto-upload="true" :limit="1"
                    :before-upload="handleLogoUpload" :show-file-list="true">
                    <template #trigger>
                        <el-button type="primary">Télécharger le logo</el-button>
                    </template>
                    <template #tip>
                        <div class="el-upload__tip">Fichiers jpg/png de moins de 500kb</div>
                    </template>
                </el-upload>
            </el-form-item>

            <!-- Nom de l'entreprise -->
            <el-form-item label="Nom de l'entreprise" label-position="top" prop="name">
                <el-input v-model="companyInfo.fields.name" size="large" placeholder="Entrez le nom de l'entreprise" />
            </el-form-item>

            <!-- Email de l'entreprise -->
            <el-form-item label="Email" label-position="top" prop="email">
                <el-input v-model="companyInfo.fields.email" size="large"
                    placeholder="Entrez l'email de l'entreprise" />
            </el-form-item>

            <!-- Adresse -->
            <el-form-item label="Adresse" label-position="top" prop="address">
                <el-input v-model="companyInfo.fields.address" size="large"
                    placeholder="Entrez l'adresse de l'entreprise" />
            </el-form-item>

            <!-- Signature -->
            <el-form-item label="Signature de l'entreprise" label-position="top" class="w-full mb-4">
                <el-image style="width: 200px; height: 100px" :src="companyInfo.fields?.signature" fit="contain"
                    class="rounded-sm mr-8" />
                <el-upload ref="uploadSignatureRef" class="upload-demo" :limit="1" :auto-upload="true"
                    :before-upload="handleSignatureUpload" :show-file-list="true">
                    <template #trigger>
                        <el-button type="primary">Télécharger la signature</el-button>
                    </template>
                    <template #tip>
                        <div class="el-upload__tip">Fichiers jpg/png de moins de 500kb</div>
                    </template>
                </el-upload>
            </el-form-item>

            <!-- Submit Button -->
            <el-form-item label-position="top">
                <el-button type="primary" class="w-full" size="large" @click="submitCompanyUpdate">
                    Enregistrer les modifications
                </el-button>
            </el-form-item>
        </el-form>
    </div>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue';
import { useCompanyStore } from '@/stores/companyStore';
import { ElMessage } from 'element-plus';

const companyStore = useCompanyStore();
const companyInfo = ref({
    fields: {
        logo: '',
        name: '',
        email: '',
        address: '',
        signature: ''
    }
});
const uploadLogoRef = ref();
const uploadSignatureRef = ref();



// Définition des règles de validation
const rules = ref({
    name: [{ required: true, message: 'Veuillez entrer le nom de l\'entreprise', trigger: 'blur' }],
    email: [
        { required: true, message: 'Veuillez entrer une adresse email', trigger: 'blur' },
        { type: 'email', message: 'Veuillez entrer une adresse email valide', trigger: ['blur', 'change'] }
    ],
    address: [{ required: true, message: 'Veuillez entrer l\'adresse de l\'entreprise', trigger: 'blur' }]
});

const formRef = ref();

onMounted(async () => {
    await companyStore.fetchCompanyInfo();
    if (companyStore.companyInfo && companyStore.companyInfo.fields) {
        companyInfo.value = { ...companyStore.companyInfo };
    } else {
        companyInfo.value = {
            fields: {
                logo: '',
                name: '',
                email: '',
                address: '',
                signature: ''
            }
        };
    }
});

const submitCompanyUpdate = async () => {
    if (!formRef.value) return;
    formRef.value.validate(async (valid) => {
        if (valid) {
            try {
                const { name, email, address } = companyInfo.value.fields;
                await companyStore.updateCompany({ name, email, address });
                ElMessage.success('Configuration de l\'entreprise mise à jour avec succès');
            } catch (error) {
                ElMessage.error('Erreur lors de la mise à jour de la configuration');
            }
        } else {
            ElMessage.error('Veuillez corriger les erreurs dans le formulaire');
        }
    });
};

const handleLogoUpload = async (file) => {
    const formData = new FormData();
    formData.append('file', file.raw || file);
    try {
        console.log(formData)
        await companyStore.uploadFile(formData, 'logo');
        ElMessage.success('Logo mis à jour avec succès');
    } catch (error) {
        ElMessage.error('Erreur lors de la mise à jour du logo');
    }
    return false;
};

const handleSignatureUpload = async (file) => {
    const formData = new FormData();
    formData.append('file', file.raw || file);
    try {
        await companyStore.uploadFile(formData, 'signature');
        ElMessage.success('Signature mise à jour avec succès');
    } catch (error) {
        ElMessage.error('Erreur lors de la mise à jour de la signature');
    }
    return false;
};
</script>

<style scoped>
.upload-demo {
    display: inline-block;
    margin-top: 10px;
}
</style>
