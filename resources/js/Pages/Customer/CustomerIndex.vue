<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Customer, CustomerOperatingSite, Relations } from '@/types/types';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import CustomerForm from './partial/CustomerForm.vue';

defineProps<{
    customers: (Customer & {
        customer_operating_sites: (CustomerOperatingSite & Pick<Relations<'customerOperatingSite'>, 'current_address'>)[];
    })[];
}>();

const search = ref('');
</script>

<template>
    <AdminLayout title="Kundenliste">
        <v-card>
            <v-data-table-virtual
                fixed-header
                :headers="[
                    { title: 'Name', key: 'name', sortable: false },
                    { title: 'E-Mail', key: 'email', sortable: false },
                    { title: 'Telefonnummer', key: 'phone', sortable: false },
                    { title: '', key: 'actions', align: 'end', sortable: false },
                ]"
                :items="customers.toSorted((a, b) => a.name.localeCompare(b.name))"
                :search="search"
            >
                <template #header.actions>
                    <div class="d-flex justify-end ga-2 align-center">
                        <v-text-field
                            hide-details
                            density="compact"
                            style="width: 200px"
                            v-model="search"
                            label="Suchen"
                            variant="outlined"
                        ></v-text-field>
                        <v-dialog max-width="1000">
                            <template v-slot:activator="{ props: activatorProps }">
                                <v-btn color="primary" v-bind="activatorProps" title="Neuen Kunden anlegen" variant="flat">
                                    <v-icon>mdi-plus</v-icon>
                                </v-btn>
                            </template>
                            <template v-slot:default="{ isActive }">
                                <CustomerForm @close="isActive.value = false" />
                            </template>
                        </v-dialog>
                    </div>
                </template>
                <template #item.actions="{ item }">
                    <v-btn
                        v-if="can('customer', 'viewShow')"
                        @click.stop="router.get(route('customer.show', { customer: item.id }))"
                        icon="mdi-eye"
                        variant="text"
                        color="primary"
                    ></v-btn>
                </template>
            </v-data-table-virtual>
        </v-card>
    </AdminLayout>
</template>
