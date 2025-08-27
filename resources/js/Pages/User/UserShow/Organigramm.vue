<script setup lang="ts">
import UserOrganigram from '@/Components/UserOrganigram.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Tree, User } from '@/types/types';
import UserShowNavBar from './partial/UserShowNavBar.vue';

defineProps<{
    user: User;

    organigramUsers: Tree<Pick<User, 'id' | 'first_name' | 'last_name' | 'email' | 'supervisor_id' | 'job_role'>, 'all_supervisees'>[];
    supervisor: Pick<User, 'id' | 'first_name' | 'last_name' | 'email' | 'job_role'> | null;
}>();
</script>
<template>
    <AdminLayout :title="user.first_name + ' ' + user.last_name">
        <UserShowNavBar :user tab="userOrganigram" />
        <UserOrganigram :users="organigramUsers" :supervisor :currentUser="user" />
    </AdminLayout>
</template>
<style lang="scss" scoped></style>
