<template>
    <el-dialog title="Détails de l'Utilisateur" v-model="visible" :show-close="false" align-center>
        <el-form :model="editableUser" ref="formRef" label-position="top">
            <el-form-item label="Prénom" prop="first_name">
                <el-input v-model="editableUser.first_name" size="large" />
            </el-form-item>

            <el-form-item label="Nom" prop="lastName">
                <el-input v-model="editableUser.last_name" size="large" />
            </el-form-item>

            <el-form-item label="Email" prop="email">
                <el-input v-model="editableUser.email" size="large" disabled />
            </el-form-item>

            <el-form-item label="Adresse" prop="address">
                <el-input v-model="editableUser.address" size="large" />
            </el-form-item>

            <el-form-item label="Numéro de Téléphone" prop="phoneNumber">
                <vue-tel-input v-model="editableUser.phone_number" class="w-full"></vue-tel-input>
            </el-form-item>

            <el-form-item label="Rôle" prop="role">
                <el-select v-model="editableUser.role" placeholder="Choisissez un rôle" size="large">
                    <el-option label="Employé" value="Employé" />
                    <el-option label="Manager" value="Manager" />
                    <el-option label="Directeur" value="Directeur" />
                </el-select>
            </el-form-item>
        </el-form>

        <template #footer>
            <el-button @click="close">Annuler</el-button>
            <el-button type="primary" @click="saveChanges">Enregistrer</el-button>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, defineEmits, watch, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { FormInstance } from 'element-plus';
import { useAuthStore } from '@/stores/authStore';

const userStore = useAuthStore();
const formRef = ref<FormInstance>();

const props = defineProps({
    user: Object
});

const emit = defineEmits(['user-updated']);

const visible = ref(true);
const editableUser = ref({ ...props.user });

watch(
    () => props.user,
    (newUser) => {
        editableUser.value = { ...newUser };
    }
);

const close = () => {
    emit('close');
};

const saveChanges = () => {
    formRef.value.validate(async (valid: boolean) => {
        if (valid) {
            try {
                await userStore.modifyUser(editableUser.value);
                ElMessage.success('Utilisateur modifié avec succès');
                emit('user-updated', editableUser.value);
                close();
            } catch (error) {
                ElMessage.error("Erreur lors de la modification de l'utilisateur");
                console.error(error);
            }
        }
    });
};
onMounted(() => {
    console.log(props.user)
})
</script>

<style scoped>
.el-dialog {
    width: 90%;
    max-width: 500px;
    margin: 0 auto;
}

.el-form-item {
    margin-bottom: 20px;
}

.el-input, .el-select {
    width: 100%;
}

@media (max-width: 600px) {
    .el-dialog {
        width: 100%;
        margin: 0;
        border-radius: 0;
    }

    .el-dialog__header, .el-dialog__body, .el-dialog__footer {
        padding: 10px;
    }

    .el-form-item {
        margin-bottom: 15px;
    }
}
</style>

