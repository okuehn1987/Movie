<script setup lang="ts">
import { Organization } from '@/types/types';

const props = defineProps<{
    organization: Organization;
}>();

const organizationForm = useForm({
    name: props.organization.name,
    tax_registration_id: props.organization.tax_registration_id,
    commercial_registration_id: props.organization.commercial_registration_id,
    logo: null,
    website: props.organization.website,
    night_surcharges: props.organization.night_surcharges,
    vacation_limitation_period: props.organization.vacation_limitation_period,
});
function submit() {
    organizationForm
        .transform(data => ({
            ...data,
            _method: 'patch',
        }))
        .post(
            route('organization.update.post', {
                organization: props.organization.id,
            }),
        );
}
</script>
<template>
    <v-card>
        <v-card-text>
            <v-form @submit.prevent="submit" :disabled="!can('organization', 'update')">
                <v-row>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="organizationForm.name"
                            label="Firmenname"
                            :error-messages="organizationForm.errors.name"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="organizationForm.tax_registration_id"
                            label="Umsatzsteuer-ID (optional)"
                            :error-messages="organizationForm.errors.tax_registration_id"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="organizationForm.commercial_registration_id"
                            label="Handelsregister (optional)"
                            :error-messages="organizationForm.errors.commercial_registration_id"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="organizationForm.website"
                            label="Webseite (optional)"
                            :error-messages="organizationForm.errors.website"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-file-input
                            :label="$page.props.organization.logo ? 'Firmenlogo ändern' : 'Firmenlogo (optional)'"
                            v-model="organizationForm.logo"
                            :error-messages="organizationForm.errors.logo"
                            accept="image/*"
                        ></v-file-input>
                    </v-col>

                    <!-- <v-col cols="12" md="3">
                        <v-checkbox
                            label="Nachtzuschüsse?"
                            v-model="organizationForm.night_surcharges"
                            :error-messages="organizationForm.errors.night_surcharges"
                        ></v-checkbox>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-checkbox
                            label="Verjährungsfrist bei Urlaubstagen?"
                            v-model="organizationForm.vacation_limitation_period"
                            :error-messages="organizationForm.errors.vacation_limitation_period"
                        ></v-checkbox>
                    </v-col> -->

                    <v-col cols="12" class="text-end" v-if="can('organization', 'update')">
                        <v-btn type="submit" color="primary">Aktualisieren</v-btn>
                    </v-col>
                </v-row>
            </v-form>
        </v-card-text>
    </v-card>
</template>
