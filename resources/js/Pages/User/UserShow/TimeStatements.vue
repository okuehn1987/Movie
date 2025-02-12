<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserShowNavBar from './partial/UserShowNavBar.vue';
import { User, WorkLog } from '@/types/types';

defineProps<{
    user: User;
    work_logs: WorkLog[];
}>();
</script>
<template>
    <AdminLayout :title="user.first_name + ' ' + user.last_name">
        <UserShowNavBar :user tab="timeStatements" />
        <v-card>
            <v-btn download :href="route('user.timeStatementDoc', { user: user.id })">getDoc</v-btn>
        </v-card>
    </AdminLayout>
</template>
