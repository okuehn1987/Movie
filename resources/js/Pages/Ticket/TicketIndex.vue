<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TicketOverview from './partials/TicketOverview.vue';
import { CustomerOperatingSiteProp, CustomerProp, OperatingSiteProp, TicketProp, UserProp } from './partials/ticketTypes';

defineProps<{
    tickets: TicketProp[];
    archiveTickets: TicketProp[];
    customers: CustomerProp[];
    users: UserProp[];
    operatingSites: OperatingSiteProp[];
    customerOperatingSites: CustomerOperatingSiteProp[];
}>();
</script>
<template>
    <AdminLayout title="Tickets">
        <TicketOverview :tickets :archiveTickets :customers :users :operatingSites :customerOperatingSites></TicketOverview>
    </AdminLayout>
</template>
