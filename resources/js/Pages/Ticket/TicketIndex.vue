<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TicketOverview from './partials/TicketOverview.vue';
import { CustomerProp, OperatingSiteProp, TicketProp, UserProp } from './partials/ticketTypes';

defineProps<{
    tickets: TicketProp[];
    archiveTickets: TicketProp[];
    customers: CustomerProp[];
    users: UserProp[];
    operatingSites: OperatingSiteProp[];
}>();
</script>
<template>
    <AdminLayout title="Tickets">
        <TicketOverview :tickets :archiveTickets :customers :users :operatingSites></TicketOverview>
    </AdminLayout>
</template>
