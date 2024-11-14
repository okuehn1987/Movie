<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { DBObject, TimeAccount, User } from '@/types/types';
defineProps<{
    users: (Pick<User, 'id' | 'first_name' | 'last_name'> & { time_accounts: Omit<TimeAccount, keyof DBObject | 'deleted_at'>[] })[];
}>();
</script>
<template>
    <AdminLayout title="Arbeitszeitkonten">
        <v-card>
            <v-data-table
                :items="users.flatMap(u => u.time_accounts.map(t => JSON.parse(JSON.stringify({ ...u, time_accounts: undefined, ...t }))))"
            ></v-data-table>
        </v-card>
    </AdminLayout>
</template>
