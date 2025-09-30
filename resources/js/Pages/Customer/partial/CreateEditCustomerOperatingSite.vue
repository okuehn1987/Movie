<script setup lang="ts">
import { Customer, CustomerOperatingSite, Relations } from '@/types/types';

const props = defineProps<{
    customer: Customer;
    mode: 'edit' | 'create';
    item: (CustomerOperatingSite & Pick<Relations<'customerOperatingSite'>, 'current_address'>) | null;
}>();

const operatingSiteForm = useForm({
    name: props.item?.name ?? '',
    street: props.item?.current_address.street ?? '',
    house_number: props.item?.current_address.house_number ?? '',
    address_suffix: props.item?.current_address.address_suffix ?? '',
    country: props.item?.current_address.country ?? '',
    city: props.item?.current_address.city ?? '',
    zip: props.item?.current_address.zip ?? '',
    federal_state: props.item?.current_address.federal_state ?? '',
});
</script>
<template>
    <v-dialog max-width="1000">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn
                variant="flat"
                color="primary"
                v-bind="activatorProps"
                :title="mode == 'create' ? 'Neuen Standort anlegen' : 'Standort bearbeiten'"
            >
                <v-icon>{{ mode == 'create' ? 'mdi-plus' : 'mdi-pencil' }}</v-icon>
            </v-btn>
        </template>
        <template v-slot:default="{ isActive }">
            <v-card :title="mode == 'create' ? 'Neuen Standort anlegen' : 'Standort bearbeiten'">
                {{ item }}
                <template #append>
                    <v-btn icon variant="text" @click.stop="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-divider></v-divider>
                <v-card-text>
                    <v-form
                        @submit.prevent="
                            item
                                ? operatingSiteForm.patch(route('customerOperatingSite.update', { customerOperatingSite: item.id }))
                                : operatingSiteForm.post(route('customer.customerOperatingSite.store', { customer: customer.id }))
                        "
                    >
                        <v-row>
                            <v-col cols="12" md="12">
                                <v-text-field
                                    label="Name"
                                    v-model="operatingSiteForm.name"
                                    :error-messages="operatingSiteForm.errors.name"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-text-field
                                    label="Straße"
                                    v-model="operatingSiteForm.street"
                                    :error-messages="operatingSiteForm.errors.street"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="2">
                                <v-text-field
                                    label="Hausnummer"
                                    v-model="operatingSiteForm.house_number"
                                    :error-messages="operatingSiteForm.errors.house_number"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Adresszusatz"
                                    v-model="operatingSiteForm.address_suffix"
                                    :error-messages="operatingSiteForm.errors.address_suffix"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="2">
                                <v-text-field
                                    label="Postleitzahl"
                                    v-model="operatingSiteForm.zip"
                                    :error-messages="operatingSiteForm.errors.zip"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-text-field
                                    label="Stadt"
                                    v-model="operatingSiteForm.city"
                                    :error-messages="operatingSiteForm.errors.city"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Bundesland"
                                    v-model="operatingSiteForm.federal_state"
                                    :error-messages="operatingSiteForm.errors.federal_state"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Land"
                                    v-model="operatingSiteForm.country"
                                    :error-messages="operatingSiteForm.errors.country"
                                ></v-text-field>
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
</template>
