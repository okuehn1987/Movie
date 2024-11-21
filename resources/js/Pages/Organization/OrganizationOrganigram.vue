<script setup lang="ts">
import UserOrganigram from '@/Components/UserOrganigram.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Tree, User } from '@/types/types';

defineProps<{
    users: Tree<Pick<User, 'id' | 'first_name' | 'last_name' | 'supervisor_id' | 'email'>, 'all_supervisees'>[];
}>();
</script>
<template>
    <AdminLayout title="Organigramm">
        <UserOrganigram :users :currentUser="$page.props.auth.user" />
    </AdminLayout>
</template>
