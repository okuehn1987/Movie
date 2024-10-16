<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Group, OperatingSite, User, UserPermission } from '@/types/types';
import UserForm from './UserForm.vue';

defineProps<{
    user: User;
    groups: Group[];
    operating_sites: OperatingSite[];
    permissions: UserPermission[];
}>();
</script>
<template>
    <AdminLayout title="User Show">
        <v-container>
            <v-card>
                <UserForm :user :groups :operating_sites :permissions mode="edit"></UserForm>
            </v-card>
        </v-container>
    </AdminLayout>
</template>
