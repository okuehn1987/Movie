<script setup lang="ts">
import { OperatingSite } from '@/types/types';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    operatingSite: OperatingSite;
}>();

const operatingSiteForm = useForm({
    email: props.operatingSite.email,
    fax: props.operatingSite.fax,
    phone_number: props.operatingSite.phone_number,
    street: props.operatingSite.street,
    country: props.operatingSite.country,
    city: props.operatingSite.city,
    address_suffix: props.operatingSite.address_suffix,
    house_number: props.operatingSite.house_number,
    federal_state: props.operatingSite.federal_state,
    zip: props.operatingSite.zip,
    name: props.operatingSite.name,
    is_headquarter: props.operatingSite.is_headquarter,
});

function submit() {
    operatingSiteForm.patch(
        route('operatingSite.update', {
            operatingSite: props.operatingSite.id,
        }),
        {
            onSuccess: () => operatingSiteForm.reset(),
        },
    );
}
</script>
<template>
    <v-card>
        <v-card-text>
            <v-form @submit.prevent="submit" :disabled="!can('operatingSite', 'update')">
                <v-row>
                    <v-col cols="12"><h3>Kontaktinformationen</h3></v-col>

                    <v-col cols="12" md="6">
                        <v-text-field label="Name" v-model="operatingSiteForm.name" :error-messages="operatingSiteForm.errors.name"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field label="Email" v-model="operatingSiteForm.email" :error-messages="operatingSiteForm.errors.email"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            label="Telefonnummer"
                            v-model="operatingSiteForm.phone_number"
                            :error-messages="operatingSiteForm.errors.phone_number"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field label="Fax" v-model="operatingSiteForm.fax" :error-messages="operatingSiteForm.errors.fax"></v-text-field>
                    </v-col>

                    <v-col cols="12"><h3>Adresse</h3></v-col>

                    <v-col cols="12" md="4">
                        <v-text-field
                            label="Straße"
                            v-model="operatingSiteForm.street"
                            :error-messages="operatingSiteForm.errors.street"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-text-field
                            label="Hausnummer"
                            v-model="operatingSiteForm.house_number"
                            :error-messages="operatingSiteForm.errors.house_number"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-text-field
                            label="Adresszusatz"
                            v-model="operatingSiteForm.address_suffix"
                            :error-messages="operatingSiteForm.errors.address_suffix"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            label="Postleitzahl"
                            v-model="operatingSiteForm.zip"
                            :error-messages="operatingSiteForm.errors.zip"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field label="Ort" v-model="operatingSiteForm.city" :error-messages="operatingSiteForm.errors.city"></v-text-field>
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
                            :error-messages="operatingSiteForm.errors.country"
                            v-model="operatingSiteForm.country"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-checkbox
                            label="Hauptsitz?"
                            v-model="operatingSiteForm.is_headquarter"
                            :error-messages="operatingSiteForm.errors.is_headquarter"
                        ></v-checkbox>
                    </v-col>
                    <v-col cols="12" class="text-end" v-if="can('operatingSite', 'update')">
                        <v-btn :loading="operatingSiteForm.processing" type="submit" color="primary">Aktualisieren</v-btn>
                    </v-col>
                </v-row>
            </v-form>
        </v-card-text>
    </v-card>
</template>
