<script setup lang="ts">
import ConfirmDelete from '@/Components/ConfirmDelete.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Customer, CustomerNoteEntry, CustomerNoteFolder, CustomerOperatingSite, Paginator, RelationPick, Relations, Tree } from '@/types/types';
import { formatAddress } from '@/utils';
import { ref } from 'vue';
import TicketOverview from '../Ticket/partials/TicketOverview.vue';
import { OperatingSiteProp, TicketProp, Tab as TicketTab, UserProp } from '../Ticket/partials/ticketTypes';
import CreateEditCustomerOperatingSite from './partial/CreateEditCustomerOperatingSite.vue';
import CustomerContactDialog from './partial/CustomerContactDialog.vue';
import CustomerForm from './partial/CustomerForm.vue';
import CustomerNotes from './partial/CustomerNotes.vue';

type Tab = 'customerData' | 'customerNotes' | 'tickets';
const props = defineProps<{
    customer: Customer & Pick<Relations<'customer'>, 'contacts'>;
    operatingSites: (CustomerOperatingSite & Pick<Relations<'customerOperatingSite'>, 'current_address'>)[];
    customerNoteFolders: Tree<Pick<CustomerNoteFolder, 'id' | 'customer_id' | 'name'>, 'sub_folders'>[];
    customerNoteEntries: (CustomerNoteEntry & RelationPick<'customerNoteEntry', 'user', 'first_name' | 'last_name'>)[];
    users: UserProp[];
    ticketableOperatingSites: OperatingSiteProp[];
    tickets: TicketProp[];
    archiveTickets: Paginator<TicketProp>;
    ticketTab: TicketTab;
    tab: Tab;
}>();
const currentTab = ref<Tab>(props.tab);
</script>
<template>
    <AdminLayout :title="'Kunde: ' + customer.name" :backurl="route('customer.index')">
        <v-tabs v-model="currentTab">
            <v-tab value="customerData">Stammdaten</v-tab>
            <v-tab value="customerNotes">Unterlagen</v-tab>
            <v-tab value="tickets">Tickets</v-tab>
        </v-tabs>
        <v-tabs-window :model-value="currentTab">
            <v-tabs-window-item value="customerData">
                <CustomerForm :customer="customer" />
                <v-card title="Standort" class="mt-4">
                    <v-data-table
                        fixed-header
                        :headers="[
                            { title: 'Name', key: 'name', sortable: false },
                            { title: 'Adresse', key: 'address', sortable: false },
                            { title: '', key: 'actions', align: 'end' },
                        ]"
                        :items="operatingSites.map(o => ({ ...o, address: formatAddress(o.current_address) }))"
                    >
                        <template #header.actions>
                            <CreateEditCustomerOperatingSite
                                v-if="can('customerOperatingSite', 'create')"
                                :customer="customer"
                            ></CreateEditCustomerOperatingSite>
                        </template>
                        <template #item.actions="{ item }">
                            <v-btn
                                color="primary"
                                variant="text"
                                target="_blank"
                                :href="`https://www.google.com/maps/place/${formatAddress(item.current_address)}/`"
                            >
                                <v-icon>mdi-map-marker-multiple</v-icon>
                            </v-btn>
                            <CreateEditCustomerOperatingSite
                                v-if="can('customerOperatingSite', 'update')"
                                :customer="customer"
                                :item
                            ></CreateEditCustomerOperatingSite>
                            <ConfirmDelete
                                v-if="can('customerOperatingSite', 'delete')"
                                title="Kundenstandort löschen"
                                :content="'Bist du dir sicher, dass du den Standort ' + item.name + ' löschen möchtest?'"
                                :route="route('customerOperatingSite.destroy', { customerOperatingSite: item.id })"
                            ></ConfirmDelete>
                        </template>
                    </v-data-table>
                </v-card>
                <v-card title="Kontakte" class="mt-4">
                    <v-data-table
                        fixed-header
                        :headers="[
                            { title: 'Name', key: 'name', sortable: false },
                            { title: 'Tätigkeit', key: 'occupation', sortable: false },
                            { title: 'Telefonnummer', key: 'phone_number', sortable: false },
                            { title: 'Mobiltelefonnummer', key: 'mobile_number', sortable: false },
                            { title: 'Email', key: 'email', sortable: false },
                            { title: '', key: 'actions', align: 'end' },
                        ]"
                        :items="customer.contacts"
                    >
                        <template #header.actions>
                            <CustomerContactDialog v-if="can('customerContact', 'create')" :customer="customer"></CustomerContactDialog>
                        </template>
                        <template #item.phone_number="{ item }">
                            <a v-if="item.phone_number" class="text-decoration-none text-black py-1" :href="`tel:${item.phone_number}`">
                                <v-icon variant="text">mdi-phone</v-icon>
                                {{ item.phone_number }}
                            </a>
                        </template>
                        <template #item.mobile_number="{ item }">
                            <a v-if="item.mobile_number" class="text-decoration-none text-black py-1" :href="`tel:${item.mobile_number}`">
                                <v-icon variant="text">mdi-phone</v-icon>
                                {{ item.mobile_number }}
                            </a>
                        </template>
                        <template #item.email="{ item }">
                            <a v-if="item.email" class="text-decoration-none text-black py-1" :href="`mailto:${item.email}`">
                                <v-icon variant="text">mdi-email</v-icon>
                                {{ item.email }}
                            </a>
                        </template>
                        <template #item.actions="{ item }">
                            <CustomerContactDialog
                                v-if="can('customerContact', 'update')"
                                :customer="customer"
                                :contact="item"
                            ></CustomerContactDialog>
                            <ConfirmDelete
                                v-if="can('customerContact', 'delete')"
                                title="Kundenkontakt löschen"
                                :content="'Bist du dir sicher, dass du den Kontakt ' + item.name + ' löschen möchtest?'"
                                :route="route('customerContact.destroy', { customerContact: item.id })"
                            ></ConfirmDelete>
                        </template>
                    </v-data-table>
                </v-card>
            </v-tabs-window-item>
            <v-tabs-window-item value="customerNotes">
                <CustomerNotes :customerNoteEntries :customer :customerNoteFolders="customerNoteFolders"></CustomerNotes>
            </v-tabs-window-item>
            <v-tabs-window-item value="tickets">
                <TicketOverview
                    :tickets
                    :archiveTickets
                    :customers="[customer]"
                    :users
                    :operatingSites="ticketableOperatingSites"
                    :tab="ticketTab"
                ></TicketOverview>
            </v-tabs-window-item>
        </v-tabs-window>
    </AdminLayout>
</template>
<style lang="scss" scoped></style>
