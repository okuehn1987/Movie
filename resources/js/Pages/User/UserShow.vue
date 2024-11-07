<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Group, OperatingSite, User, UserPermission, UserWorkingWeek, UserWorkingHours } from '@/types/types';
import UserForm from './UserForm.vue';

defineProps<{
    user: User & { lastWorkingHours: UserWorkingHours; userWorkingWeek: UserWorkingWeek };
    groups: Group[];
    operating_sites: OperatingSite[];
    permissions: UserPermission[];
}>();
</script>
<template>
    <AdminLayout title="User Show" :backurl="route('user.index')">
        <UserForm :user :groups :operating_sites :permissions mode="edit"></UserForm>
    </AdminLayout>
</template>
