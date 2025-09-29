<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Customer, CustomerOperatingSite, Relations } from '@/types/types';
import { ref } from 'vue';
import CustomerForm from './partial/CustomerForm.vue';
import { formatAddress } from '@/utils';
import CustomerNotes from './partial/CustomerNotes.vue';

defineProps<{
    customer: Customer;
    operatingSites: (CustomerOperatingSite & Pick<Relations<'customerOperatingSite'>, 'current_address'>)[];
    customerNotes: Relations<'customer'>['customer_notes'];
}>();

const currentTab = ref('customerData');

const operatingSiteForm = useForm({
    name: '',
    street: '',
    house_number: '',
    address_suffix: '',
    country: '',
    city: '',
    zip: '',
    federal_state: '',
});
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
                            <v-dialog max-width="1000">
                                <template v-slot:activator="{ props: activatorProps }">
                                    <v-btn variant="flat" color="primary" v-bind="activatorProps" title="Neuen Standort anlegen">
                                        <v-icon>mdi-plus</v-icon>
                                    </v-btn>
                                </template>
                                <template v-slot:default="{ isActive }">
                                    <v-card :title="'Neuen Standort anlegen'">
                                        <template #append>
                                            <v-btn icon variant="text" @click.stop="isActive.value = false">
                                                <v-icon>mdi-close</v-icon>
                                            </v-btn>
                                        </template>
                                        <v-divider></v-divider>
                                        <v-card-text>
                                            <v-form
                                                @submit.prevent="
                                                    operatingSiteForm.post(route('customer.customerOperatingSite.store', { customer: customer.id }))
                                                "
                                            >
                                                <v-row>
                                                    <v-col cols="12" md="12">
                                                        <v-text-field label="Name" v-model="operatingSiteForm.name"></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" md="4">
                                                        <v-text-field label="Straße" v-model="operatingSiteForm.street"></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" md="2">
                                                        <v-text-field label="Hausnummer" v-model="operatingSiteForm.house_number"></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" md="6">
                                                        <v-text-field label="Adresszusatz" v-model="operatingSiteForm.address_suffix"></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" md="2">
                                                        <v-text-field label="Postleitzahl" v-model="operatingSiteForm.zip"></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" md="4">
                                                        <v-text-field label="Stadt" v-model="operatingSiteForm.city"></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" md="6">
                                                        <v-text-field label="Bundesland" v-model="operatingSiteForm.federal_state"></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" md="6">
                                                        <v-text-field label="Land" v-model="operatingSiteForm.country"></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12">
                                                        <div class="text-end">
                                                            <v-btn type="submit" color="primary">Speichern</v-btn>
                                                        </div>
                                                    </v-col>
                                                </v-row>
                                            </v-form>
                                        </v-card-text>
                                    </v-card>
                                </template>
                            </v-dialog>
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
