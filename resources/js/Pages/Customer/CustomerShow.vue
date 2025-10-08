<script setup lang="ts">
import ConfirmDelete from '@/Components/ConfirmDelete.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Customer, CustomerOperatingSite, Relations, User } from '@/types/types';
import { formatAddress } from '@/utils';
import { ref } from 'vue';
import TicketOverview from '../Ticket/partials/TicketOverview.vue';
import { TicketProp } from '../Ticket/partials/ticketTypes';
import CreateEditCustomerOperatingSite from './partial/CreateEditCustomerOperatingSite.vue';
import CustomerForm from './partial/CustomerForm.vue';
import CustomerNotes from './partial/CustomerNotes.vue';

defineProps<{
    customer: Customer;
    users: User[];
    operatingSites: (CustomerOperatingSite & Pick<Relations<'customerOperatingSite'>, 'current_address'>)[];
    tickets: TicketProp[];
    archiveTickets: TicketProp[];
    customerNotes: Relations<'customer'>['customer_notes'];
}>();

const currentTab = ref('customerData');
</script>
<template>
    <AdminLayout :title="'Kunde: ' + customer.name" :backurl="route('customer.index')">
        <v-tabs v-model="currentTab">
            <v-tab value="customerData">Stammdaten</v-tab>
            <v-tab value="operatingSites">Standorte</v-tab>
            <v-tab value="customerNotes">Notizen</v-tab>
            <v-tab value="tickets">Tickets</v-tab>
        </v-tabs>
        <v-tabs-window :model-value="currentTab">
            <v-tabs-window-item value="customerData">
                <CustomerForm :customer="customer" />
            </v-tabs-window-item>
            <v-tabs-window-item value="operatingSites">
                <v-data-table-virtual
                    fixed-header
                    :headers="[
                        { title: 'Name', key: 'name', sortable: false },
                        { title: 'Adresse', key: 'address', sortable: false },
                        { title: '', key: 'actions', align: 'end' },
                    ]"
                    :items="operatingSites.map(o => ({ ...o, address: formatAddress(o.current_address) }))"
                >
                    <template #header.actions>
                        <CreateEditCustomerOperatingSite :customer="customer"></CreateEditCustomerOperatingSite>
                    </template>
                    <template #item.actions="{ item }">
                        <CreateEditCustomerOperatingSite :customer="customer" :item></CreateEditCustomerOperatingSite>
                        <ConfirmDelete
                            :title="'Kundenstandort löschen'"
                            :content="'Bist du dir sicher, dass du den Standort ' + item.name + ' löschen möchtest?'"
                            :route="route('customerOperatingSite.destroy', { customerOperatingSite: item.id })"
                        ></ConfirmDelete>
                    </template>
                </v-data-table-virtual>
            </v-tabs-window-item>
            <v-tabs-window-item value="customerNotes">
                <CustomerNotes :customer :customerNotes="customerNotes"></CustomerNotes>
            </v-tabs-window-item>
            <v-tabs-window-item value="tickets">
                <TicketOverview :users :tickets tab="newTickets" :customers="[customer]" :archiveTickets></TicketOverview>
            </v-tabs-window-item>
        </v-tabs-window>
    </AdminLayout>
</template>
<style lang="scss" scoped></style>
