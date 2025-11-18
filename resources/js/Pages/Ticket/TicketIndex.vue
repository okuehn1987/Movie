<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TicketOverview from './partials/TicketOverview.vue';
import { CustomerProp, OperatingSiteProp, Tab, TicketProp, UserProp } from './partials/ticketTypes';
import { Paginator } from '@/types/types';

defineProps<{
    tickets: TicketProp[];
    archiveTickets: Paginator<TicketProp>;
    customers: CustomerProp[];
    users: UserProp[];
    operatingSites: OperatingSiteProp[];
    tab: Tab;
}>();
</script>
<template>
    <AdminLayout title="Tickets">
        <TicketOverview :tickets :archiveTickets :customers :users :operatingSites :tab></TicketOverview>
    </AdminLayout>
</template>
