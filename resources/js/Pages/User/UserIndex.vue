<script setup lang="ts">
import ConfirmDelete from '@/Components/ConfirmDelete.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Canable, CountryProp, Group, OperatingSite, Permission, User } from '@/types/types';
import { fillNullishValues, getMaxScrollHeight } from '@/utils';
import { Link } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import UserForm from './UserForm.vue';

defineProps<{
    users: (User & Canable & { group: Pick<Group, 'id' | 'name'> })[];
    supervisors: Pick<User, 'id' | 'first_name' | 'last_name'>[];
    groups: Pick<Group, 'id' | 'name'>[];
    operating_sites: Pick<OperatingSite, 'id' | 'name'>[];
    permissions: { name: Permission[keyof Permission]; label: string }[];
    countries: CountryProp[];
}>();
</script>
<template>
    <AdminLayout title="Mitarbeiter">
        <v-card>
            <v-data-table-virtual
                fixed-header
                :style="{ maxHeight: getMaxScrollHeight(0) }"
                no-data-text="Es wurden keine Mitarbeiter gefunden"
                :headers="[
                    { title: 'Vorname', key: 'first_name' },
                    { title: 'Nachname', key: 'last_name' },
                    { title: 'Email', key: 'email' },
                    { title: 'Abteilung', key: 'group.name' },
                    { title: 'Personalnummer', key: 'staff_number' },
                    { title: 'Geburtsdatum', key: 'date_of_birth' },
                    { title: '', key: 'actions', align: 'end' },
                ]"
                :items="
                    users
                        .map(u => ({
                            ...fillNullishValues(u),
                            date_of_birth: DateTime.fromFormat(u.date_of_birth, 'yyyy-MM-dd').toFormat('dd.MM.yyyy'),
                        }))
                        .toSorted((a, b) => (a.first_name + a.last_name).localeCompare(b.first_name + b.last_name))
                "
                hover
            >
                <template v-slot:header.actions>
                    <v-dialog max-width="1200" v-if="can('user', 'create')">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn v-bind="activatorProps" color="primary">
                                <v-icon icon="mdi-plus"></v-icon>
                            </v-btn>
                        </template>
                        <template v-slot:default="{ isActive }">
                            <v-card style="overflow: auto" :style="{ maxHeight: getMaxScrollHeight(48) }">
                                <v-card-text>
                                    <v-row>
                                        <v-col cols="12">
                                            <UserForm
                                                :countries
                                                :supervisors
                                                :groups
                                                :operating_sites
                                                :permissions
                                                mode="create"
                                                @success="isActive.value = false"
                                            >
                                                <!-- TODO: add close icon back to dialog (this is the wrong spot) -->
                                                <!-- <template #append>
                                                    <v-btn icon variant="text" @click="isActive.value = false">
                                                        <v-icon>mdi-close</v-icon>
                                                    </v-btn>
                                                </template> -->
                                            </UserForm>
                                        </v-col>
                                    </v-row>
                                </v-card-text>
                            </v-card>
                        </template>
                    </v-dialog>
                </template>
                <template v-slot:item.actions="{ item }">
                    <div class="d-flex justify-end">
                        <Link
                            v-if="can('user', 'viewShow', item)"
                            :href="
                                route('user.generalInformation', {
                                    user: item.id,
                                })
                            "
                        >
                            <v-btn color="primary" variant="text" icon="mdi-eye" />
                        </Link>
                        <ConfirmDelete
                            v-if="can('user', 'delete', item)"
                            :content="'Bist du dir sicher, dass du ' + item.first_name + ' ' + item.last_name + ' entfernen möchtest?'"
                            :route="
                                route('user.destroy', {
                                    user: item.id,
                                })
                            "
                            title="Mitarbeiter löschen"
                        ></ConfirmDelete>
                        <div style="width: 48px" v-else></div>
                    </div>
                </template>
            </v-data-table-virtual>
        </v-card>
    </AdminLayout>
</template>
