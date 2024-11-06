<script setup lang="ts">
import { OperatingSite } from '@/types/types';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    operatingSite: OperatingSite;
}>();

const siteForm = useForm({
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
    is_head_quarter: props.operatingSite.is_head_quarter,
});

function submit() {
    siteForm.patch(
        route('operatingSite.update', {
            operatingSite: props.operatingSite.id,
        }),
        {
            onSuccess: () => siteForm.reset(),
        },
    );
}
</script>
<template>
    <v-card>
        <v-card-text>
            <v-form @submit.prevent="submit">
                <v-row>
                    <v-col cols="12"><h3>Kontaktinformationen</h3></v-col>

                    <v-col cols="12" md="6">
                        <v-text-field label="Name" v-model="siteForm.name" :error-messages="siteForm.errors.name"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field label="Email" v-model="siteForm.email" :error-messages="siteForm.errors.email"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            label="Telefonnummer"
                            v-model="siteForm.phone_number"
                            :error-messages="siteForm.errors.phone_number"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field label="Fax" v-model="siteForm.fax" :error-messages="siteForm.errors.fax"></v-text-field>
                    </v-col>

                    <v-col cols="12"><h3>Adresse</h3></v-col>

                    <v-col cols="12" md="4">
                        <v-text-field label="Straße" v-model="siteForm.street" :error-messages="siteForm.errors.street"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-text-field
                            label="Hausnummer"
                            v-model="siteForm.house_number"
                            :error-messages="siteForm.errors.house_number"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-text-field
                            label="Adresszusatz"
                            v-model="siteForm.address_suffix"
                            :error-messages="siteForm.errors.address_suffix"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field label="Postleitzahl" v-model="siteForm.zip" :error-messages="siteForm.errors.zip"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field label="Ort" v-model="siteForm.city" :error-messages="siteForm.errors.city"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            label="Bundesland"
                            v-model="siteForm.federal_state"
                            :error-messages="siteForm.errors.federal_state"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-checkbox
                            label="Hauptsitz?"
                            v-model="siteForm.is_head_quarter"
                            :error-messages="siteForm.errors.is_head_quarter"
                        ></v-checkbox>
                    </v-col>
                    <v-col cols="12" class="text-end">
                        <v-btn :loading="siteForm.processing" type="submit" color="primary">Aktualisieren</v-btn>
                    </v-col>
                </v-row>
            </v-form>
        </v-card-text>
    </v-card>
</template>
