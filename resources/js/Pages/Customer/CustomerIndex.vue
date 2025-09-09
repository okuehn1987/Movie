<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Customer, Relations } from '@/types/types';
import { formatAddress } from '@/utils';

defineProps<{
    customers: (Customer & Pick<Relations<'customer'>, 'current_address'>)[];
}>();
</script>

<template>
    <AdminLayout title="Kundenliste">
        <v-card>
            <v-data-table-virtual
                fixed-header
                :headers="[
                    { title: 'Name', key: 'name' },
                    { title: 'E-Mail', key: 'email' },
                    { title: 'Telefonnummer', key: 'phone' },
                    { title: 'Adresse', key: 'address' },
                    { title: '', key: 'actions' },
                    //TODO: implement search and address
                ]"
                :items="
                    customers.map(c => ({
                        ...c,
                        address: formatAddress(c.current_address),
                    }))
                "
            >
                <template #header.actions>
                    <v-dialog max-width="1000">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn color="primary" v-bind="activatorProps" variant="flat"><v-icon>mdi-plus</v-icon></v-btn>
                        </template>
                        <template v-slot:default="{ isActive }">
                            <v-card title="Neuen Kunden erstellen">
                                <template #append>
                                    <v-btn icon variant="text" @click="isActive.value = false">
                                        <v-icon>mdi-close</v-icon>
                                    </v-btn>
                                </template>
                            </v-card>
                        </template>
                    </v-dialog>
                </template>
            </v-data-table-virtual>
        </v-card>
    </AdminLayout>
</template>
