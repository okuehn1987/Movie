<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Customer, CustomerNoteEntry, CustomerNoteFolder, CustomerOperatingSite, Relations } from '@/types/types';
import { ref } from 'vue';
import CustomerForm from './partial/CustomerForm.vue';
import { formatAddress } from '@/utils';
import CustomerNotes from './partial/CustomerNotes.vue';
import CreateEditCustomerOperatingSite from './partial/CreateEditCustomerOperatingSite.vue';
import ConfirmDelete from '@/Components/ConfirmDelete.vue';
import CustomerContactDialog from './partial/CustomerContactDialog.vue';

defineProps<{
    customer: Customer & Pick<Relations<'customer'>, 'contacts'>;
    operatingSites: (CustomerOperatingSite & Pick<Relations<'customerOperatingSite'>, 'current_address'>)[];
    customerNoteFolders: Pick<CustomerNoteFolder, 'id' | 'customer_id' | 'name'>[];
    customerNoteEntries: Record<CustomerNoteFolder['id'], CustomerNoteEntry[]>;
}>();

const currentTab = ref('customerData');
</script>
<template>
    <AdminLayout :title="'Kunde: ' + customer.name" :backurl="route('customer.index')">
        <v-tabs v-model="currentTab">
            <v-tab value="customerData">Stammdaten</v-tab>
            <v-tab value="customerNotes">Unterlagen</v-tab>
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
                            { title: 'Email', key: 'email', sortable: false },
                            { title: '', key: 'actions', align: 'end' },
                        ]"
                        :items="customer.contacts"
                    >
                        <template #header.actions>
                            <CustomerContactDialog v-if="can('customerContact', 'create')" :customer="customer"></CustomerContactDialog>
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
            <v-tabs-window-item value="operatingSites"></v-tabs-window-item>
            <v-tabs-window-item value="customerNotes">
                <CustomerNotes :customerNoteEntries :customer :customerNoteFolders="customerNoteFolders"></CustomerNotes>
            </v-tabs-window-item>
        </v-tabs-window>
    </AdminLayout>
</template>
<style lang="scss" scoped></style>
