<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Country, CountryProp, Organization, RelationPick } from '@/types/types';
import { fillNullishValues, getStates } from '@/utils';
import { Link } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { Month, MonthStats, Year } from '@/types/types';
import PaymentTable from '../Isa/PaymentTable.vue';

defineProps<{
    organizations: (Organization &
        RelationPick<'organization', 'owner', 'id' | 'first_name' | 'last_name'> & {
            stats: Record<Year, Partial<Record<Month, MonthStats>>>;
        })[];
    countries: CountryProp[];
}>();

const organizationForm = useForm({
    organization_name: '',
    organization_street: '',
    organization_house_number: '',
    organization_address_suffix: '',
    organization_country: '' as Country,
    organization_city: '',
    organization_zip: '',
    organization_federal_state: '',
    head_quarter_name: '',
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    date_of_birth: '',
});

function submit() {
    organizationForm.post(route('organization.store'), {
        onSuccess: () => organizationForm.reset(),
    });
}
</script>
<template>
    <AdminLayout title="Organisationen">
        <v-row>
            <v-col cols="12">
                <v-card>
                    <v-data-table-virtual
                        hover
                        :headers="[
                            { title: 'id', key: 'id' },
                            { title: 'Owner', key: 'owner_name' },
                            { title: 'Name', key: 'name' },
                            { title: 'Erstellt am', key: 'created_at' },
                            { title: '', key: 'actions', width: '1px', align: 'end' },
                        ]"
                        :items="
                            organizations.map(({ stats, ...o }) => ({
                                ...fillNullishValues(o),
                                stats,
                                owner_name: o.owner.first_name + ' ' + o.owner.last_name,
                                created_at: DateTime.fromISO(o.created_at).toFormat('dd.MM.yyyy'),
                            }))
                        "
                    >
                        <template v-slot:header.actions>
                            <v-dialog max-width="1000">
                                <template v-slot:activator="{ props: activatorProps }">
                                    <v-btn v-bind="activatorProps" color="primary">
                                        <v-icon icon="mdi-plus"></v-icon>
                                    </v-btn>
                                </template>

                                <template v-slot:default="{ isActive }">
                                    <v-card title="Organisation erstellen">
                                        <template #append>
                                            <v-btn
                                                icon
                                                variant="text"
                                                @click="
                                                    isActive.value = false;
                                                    organizationForm.reset();
                                                "
                                            >
                                                <v-icon>mdi-close</v-icon>
                                            </v-btn>
                                        </template>
                                        <v-divider />
                                        <v-card-text>
                                            <v-form @submit.prevent="submit">
                                                <v-row>
                                                    <v-col cols="12"><h3>Adresse</h3></v-col>
                                                    <v-col cols="12" md="6">
                                                        <v-text-field
                                                            label="Firmenname"
                                                            required
                                                            :error-messages="organizationForm.errors.organization_name"
                                                            v-model="organizationForm.organization_name"
                                                        ></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" md="6">
                                                        <v-text-field
                                                            label="Standortname"
                                                            required
                                                            :error-messages="organizationForm.errors.head_quarter_name"
                                                            v-model="organizationForm.head_quarter_name"
                                                        ></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" sm="6">
                                                        <v-select
                                                            data-testid="organizationLand"
                                                            label="Land"
                                                            required
                                                            :items="countries.map(country => ({ title: country.title, value: country.value }))"
                                                            :error-messages="organizationForm.errors.organization_country"
                                                            v-model="organizationForm.organization_country"
                                                        ></v-select>
                                                    </v-col>
                                                    <v-col cols="12" sm="6">
                                                        <v-select
                                                            data-testid="federalState"
                                                            label="Bundesland"
                                                            :items="getStates(organizationForm.organization_country, countries)"
                                                            :disabled="!organizationForm.organization_country"
                                                            required
                                                            :error-messages="organizationForm.errors.organization_federal_state"
                                                            v-model="organizationForm.organization_federal_state"
                                                        ></v-select>
                                                    </v-col>
                                                    <v-col cols="12" sm="6">
                                                        <v-text-field
                                                            label="PLZ"
                                                            required
                                                            :error-messages="organizationForm.errors.organization_zip"
                                                            v-model="organizationForm.organization_zip"
                                                        ></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" sm="6">
                                                        <v-text-field
                                                            label="Ort"
                                                            required
                                                            :error-messages="organizationForm.errors.organization_city"
                                                            v-model="organizationForm.organization_city"
                                                        ></v-text-field>
                                                    </v-col>

                                                    <v-col cols="12" sm="6">
                                                        <v-text-field
                                                            label="Straße"
                                                            required
                                                            :error-messages="organizationForm.errors.organization_street"
                                                            v-model="organizationForm.organization_street"
                                                        ></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" sm="6">
                                                        <v-text-field
                                                            label="Hausnummer"
                                                            required
                                                            :error-messages="organizationForm.errors.organization_house_number"
                                                            v-model="organizationForm.organization_house_number"
                                                        ></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" sm="6">
                                                        <v-text-field
                                                            label="Addresszusatz (optional)"
                                                            :error-messages="organizationForm.errors.organization_address_suffix"
                                                            v-model="organizationForm.organization_address_suffix"
                                                        ></v-text-field>
                                                    </v-col>

                                                    <v-col cols="12"><h3>Admin Account</h3></v-col>

                                                    <v-col cols="12" sm="6">
                                                        <v-text-field
                                                            label="Vorname"
                                                            required
                                                            :error-messages="organizationForm.errors.first_name"
                                                            v-model="organizationForm.first_name"
                                                        ></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" sm="6">
                                                        <v-text-field
                                                            label="Nachname"
                                                            required
                                                            :error-messages="organizationForm.errors.last_name"
                                                            v-model="organizationForm.last_name"
                                                        ></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" sm="6">
                                                        <v-text-field
                                                            label="E-Mail"
                                                            required
                                                            :error-messages="organizationForm.errors.email"
                                                            v-model="organizationForm.email"
                                                        ></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" sm="6">
                                                        <v-text-field
                                                            label="Password"
                                                            required
                                                            :error-messages="organizationForm.errors.password"
                                                            v-model="organizationForm.password"
                                                        ></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" sm="6">
                                                        <v-text-field
                                                            type="date"
                                                            label="Geburtsdatum"
                                                            required
                                                            :error-messages="organizationForm.errors.date_of_birth"
                                                            v-model="organizationForm.date_of_birth"
                                                        ></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" class="text-end">
                                                        <v-btn type="submit" color="primary">Erstellen</v-btn>
                                                    </v-col>
                                                </v-row>
                                            </v-form>
                                        </v-card-text>
                                    </v-card>
                                </template>
                            </v-dialog>
                        </template>
                        <template v-slot:item.actions="{ item }">
                            <div class="d-flex ga-2 align-center">
                                <v-dialog>
                                    <template #activator="{ props: activatorProps }">
                                        <v-btn color="primary" variant="text" icon="mdi-currency-eur" v-bind="activatorProps"></v-btn>
                                    </template>
                                    <template #default="{ isActive }">
                                        <v-card :title="'Kostenübersicht der Organisation ' + item.name">
                                            <v-card-title></v-card-title>
                                            <template #append>
                                                <v-btn icon variant="text" @click.stop="isActive.value = false">
                                                    <v-icon>mdi-close</v-icon>
                                                </v-btn>
                                            </template>
                                            <v-card-text>
                                                <PaymentTable :stats="item.stats"></PaymentTable>
                                            </v-card-text>
                                        </v-card>
                                    </template>
                                </v-dialog>
                                <Link :href="route('organization.show', { organization: item.id })">
                                    <v-btn color="primary" variant="text" icon="mdi-eye" />
                                </Link>
                            </div>
                        </template>
                    </v-data-table-virtual>
                </v-card>
            </v-col>
        </v-row>
    </AdminLayout>
</template>
