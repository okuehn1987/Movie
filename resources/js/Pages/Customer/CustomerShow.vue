<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Customer, CustomerOperatingSite, Relations } from '@/types/types';
import { ref } from 'vue';
import CustomerForm from './partial/CustomerForm.vue';
import { formatAddress } from '@/utils';
import CustomerNotes from './partial/CustomerNotes.vue';
import CreateEditCustomerOperatingSite from './partial/CreateEditCustomerOperatingSite.vue';

defineProps<{
    customer: Customer;
    operatingSites: (CustomerOperatingSite & Pick<Relations<'customerOperatingSite'>, 'current_address'>)[];
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
                        { title: '', key: 'actions' },
                    ]"
                    :items="operatingSites.map(o => ({ ...o, address: formatAddress(o.current_address) }))"
                >
                    <template #header.actions>
                        <div class="d-flex justify-end ga-2 align-center">
                            <CreateEditCustomerOperatingSite :mode="'create'" :customer="customer" :item="null"></CreateEditCustomerOperatingSite>
                        </div>
                    </template>
                    <template #item.actions="{ item }">
                        <div class="d-flex justify-end ga-2 align-center">
                            <CreateEditCustomerOperatingSite :mode="'edit'" :customer="customer" :item></CreateEditCustomerOperatingSite>
                        </div>
                    </template>
                </v-data-table-virtual>
            </v-tabs-window-item>
            <v-tabs-window-item value="customerNotes">
                <CustomerNotes :customer :customerNotes="customerNotes"></CustomerNotes>
            </v-tabs-window-item>
        </v-tabs-window>
    </AdminLayout>
</template>
<style lang="scss" scoped></style>
