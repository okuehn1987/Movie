<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DeleteUserForm from "@/Pages/Profile/Partials/DeleteUserForm.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";
defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();
</script>

<template>
    <AdminLayout title="Profil" tooltip="Profile">
        <v-container>
            <v-row>
                <v-col cols="12" sm="6">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </v-col>
                <v-col cols="12" sm="6">
                    <UpdatePasswordForm />
                </v-col>
                <v-col cols="12" sm="6">
                    <DeleteUserForm />
                </v-col>
            </v-row>
        </v-container>
    </AdminLayout>
</template>
