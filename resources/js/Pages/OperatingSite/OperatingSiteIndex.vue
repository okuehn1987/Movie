<script setup lang="ts">
import ConfirmDelete from '@/Components/ConfirmDelete.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Country, CountryProp, Canable, Count, OperatingSite, Paginator, User } from '@/types/types';
import { fillNullishValues, getStates, usePagination } from '@/utils';
import { Link, useForm } from '@inertiajs/vue3';
import { toRefs } from 'vue';

const props = defineProps<{
    operatingSites: Paginator<OperatingSite & Canable & Count<User>>;
    countries: CountryProp[];
}>();

const { currentPage, lastPage, data } = usePagination(toRefs(props), 'operatingSites');

const operatingSiteForm = useForm({
    address_suffix: '',
    city: '',
    country: '' as Country,
    email: '',
    fax: '',
    federal_state: '',
    house_number: '',
    is_headquarter: false,
    phone_number: '',
    street: '',
    zip: '',
    name: '',
});

function submit() {
    operatingSiteForm.post(route('operatingSite.store'), {
        onSuccess: () => operatingSiteForm.reset(),
    });
}
</script>
<template>
    <AdminLayout title="Betriebsstätten">
        <v-card>
            <v-data-table-virtual
                hover
                :headers="[
                    { title: '#', key: 'id' },
                    { title: 'Name', key: 'name' },
                    { title: 'Anschrift', key: 'streetAddress' },
                    { title: 'Stadt', key: 'cityAddress' },
                    { title: 'Land', key: 'country' },
                    { title: 'Email', key: 'email' },
                    { title: 'Telefonnummer', key: 'phone_number' },
                    { title: '', key: 'action', align: 'end', width: 0 },
                ]"
                :items="
                    data.map(o => {
                        const streetAddress = o.street && o.house_number ? o.street + ' ' + o.house_number : '/';
                        const cityAddress = o.zip && o.city ? o.zip + ' ' + o.city : '/';

                        return { ...fillNullishValues(o), streetAddress, cityAddress };
                    })
                "
            >
                <template v-slot:header.action>
                    <v-dialog v-if="can('operatingSite', 'create')" max-width="1000">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn v-bind="activatorProps" color="primary">
                                <v-icon icon="mdi-plus"></v-icon>
                            </v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card title="Betriebsstätte erstellen">
                                <template #append>
                                    <v-btn icon variant="text" @click="isActive.value = false">
                                        <v-icon>mdi-close</v-icon>
                                    </v-btn>
                                </template>
                                <v-divider></v-divider>
                                <v-card-text>
                                    <v-form @submit.prevent="submit">
                                        <v-row>
                                            <v-col cols="12"><h3>Kontaktinformationen</h3></v-col>

                                            <v-col cols="12" md="6">
                                                <v-text-field
                                                    label="Name"
                                                    required
                                                    :error-messages="operatingSiteForm.errors.street"
                                                    v-model="operatingSiteForm.name"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-text-field
                                                    label="E-Mail "
                                                    required
                                                    :error-messages="operatingSiteForm.errors.email"
                                                    v-model="operatingSiteForm.email"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-text-field
                                                    label="Telefonnummer"
                                                    required
                                                    :error-messages="operatingSiteForm.errors.phone_number"
                                                    v-model="operatingSiteForm.phone_number"
                                                ></v-text-field>
                                            </v-col>

                                            <v-col cols="12"><h3>Adresse</h3></v-col>

                                            <v-col cols="12" md="6">
                                                <v-text-field
                                                    label="Straße"
                                                    required
                                                    :error-messages="operatingSiteForm.errors.street"
                                                    v-model="operatingSiteForm.street"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-text-field
                                                    label="Hausnummer"
                                                    required
                                                    :error-messages="operatingSiteForm.errors.house_number"
                                                    v-model="operatingSiteForm.house_number"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-text-field
                                                    label="Addresszusatz"
                                                    :error-messages="operatingSiteForm.errors.address_suffix"
                                                    v-model="operatingSiteForm.address_suffix"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-text-field
                                                    label="PLZ"
                                                    required
                                                    :error-messages="operatingSiteForm.errors.zip"
                                                    v-model="operatingSiteForm.zip"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-text-field
                                                    label="Ort"
                                                    required
                                                    :error-messages="operatingSiteForm.errors.city"
                                                    v-model="operatingSiteForm.city"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-select
                                                    label="Land"
                                                    required
                                                    :items="countries.map(country => ({ title: country.title, value: country.value }))"
                                                    :error-messages="operatingSiteForm.errors.country"
                                                    v-model="operatingSiteForm.country"
                                                ></v-select>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-select
                                                    label="Bundesland"
                                                    :items="getStates(operatingSiteForm.country, countries)"
                                                    :disabled="!operatingSiteForm.country"
                                                    required
                                                    :error-messages="operatingSiteForm.errors.federal_state"
                                                    v-model="operatingSiteForm.federal_state"
                                                ></v-select>
                                            </v-col>

                                            <v-col cols="12" md="6">
                                                <v-checkbox v-model="operatingSiteForm.is_headquarter" label="Hauptsitz?"></v-checkbox>
                                            </v-col>

                                            <v-col cols="12" class="text-end">
                                                <v-btn :loading="operatingSiteForm.processing" type="submit" color="primary">Erstellen</v-btn>
                                            </v-col>
                                        </v-row>
                                    </v-form>
                                </v-card-text>
                            </v-card>
                        </template>
                    </v-dialog>
                </template>
                <template v-slot:item.action="{ item }">
                    <div class="d-flex" :class="{ 'justify-end': !data.some(o => o.users_count == 0 && can('operatingSite', 'delete', o)) }">
                        <Link :href="route('operatingSite.show', { operatingSite: item.id })" v-if="can('operatingSite', 'viewShow', item)">
                            <v-btn color="primary" size="large" variant="text" icon="mdi-eye" />
                        </Link>
                        <ConfirmDelete
                            v-if="item.users_count == 0 && can('operatingSite', 'delete', item)"
                            :content="'Bist du dir sicher, dass du die Betriebsstätte ' + item.name + ' entfernen möchtest?'"
                            :route="
                                route('operatingSite.destroy', {
                                    operatingSite: item.id,
                                })
                            "
                            title="Betriebsstätte löschen"
                        ></ConfirmDelete>
                    </div>
                </template>
                <template v-slot:bottom>
                    <v-pagination v-if="lastPage > 1" v-model="currentPage" :length="lastPage"></v-pagination>
                </template>
            </v-data-table-virtual>
        </v-card>
    </AdminLayout>
</template>
