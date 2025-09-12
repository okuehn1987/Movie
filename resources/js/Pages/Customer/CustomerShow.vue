<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Customer, CustomerNote, CustomerOperatingSite, Relations, Tree } from '@/types/types';
import { computed, ref, shallowRef } from 'vue';
import CustomerForm from './partial/CustomerForm.vue';
import { filterTree, formatAddress, mapTree } from '@/utils';
import CustomerNotes from './partial/CustomerNotes.vue';

const props = defineProps<{
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
                    ]"
                    :items="operatingSites.map(o => ({ ...o, address: formatAddress(o.current_address) }))"
                ></v-data-table-virtual>
            </v-tabs-window-item>
            <v-tabs-window-item value="customerNotes">
                <CustomerNotes :customer :customerNotes="customerNotes"></CustomerNotes>
            </v-tabs-window-item>
        </v-tabs-window>
    </AdminLayout>
</template>
<style lang="scss" scoped></style>
