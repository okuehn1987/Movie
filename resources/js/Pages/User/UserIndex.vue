<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Group, OperatingSite, User, UserPermission } from '@/types/types';
import { Link } from '@inertiajs/vue3';
import UserForm from './UserForm.vue';
import { fillNullishValues } from '@/utils';

defineProps<{
    users: (User & { group: Pick<Group, 'id' | 'name'> })[];
    groups: Group[];
    operating_sites: OperatingSite[];
    permissions: UserPermission[];
}>();
</script>
<template>
    <AdminLayout title="Mitarbeiter">
        <v-container>
            <v-data-table-virtual
                :headers="[
                    { title: '#', key: 'id' },
                    { title: 'Vorname', key: 'first_name' },
                    { title: 'Nachname', key: 'last_name' },
                    { title: 'Email', key: 'email' },
                    { title: 'Abteilung', key: 'group.name' },
                    { title: 'Personalnummer', key: 'staff_number' },
                    { title: 'Geburtsdatum', key: 'date_of_birth' },
                    { title: '', key: 'actions', align: 'end' },
                ]"
                :items="users.map(u => fillNullishValues(u))"
                hover
            >
                <template v-slot:header.actions>
                    <v-dialog max-width="1000">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn v-bind="activatorProps" color="primary">
                                <v-icon icon="mdi-plus"></v-icon>
                            </v-btn>
                        </template>

                        <v-card>
                            <UserForm :groups :operating_sites :permissions mode="create"></UserForm>
                        </v-card>
                    </v-dialog>
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-btn
                        :href="
                            route('user.show', {
                                user: item.id,
                            })
                        "
                        color="primary"
                        class="me-2"
                    >
                        <v-icon size="large" icon="mdi-pencil"></v-icon>
                    </v-btn>
                    <v-dialog>
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn v-bind="activatorProps" color="error">
                                <v-icon size="large" icon="mdi-delete"></v-icon>
                            </v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card>
                                <v-toolbar color="primary" class="mb-4" title="Mitarbeiter löschen"></v-toolbar>
                                <v-card-text>
                                    Bist du dir sicher, dass du
                                    {{ item.first_name }}
                                    {{ item.last_name }} entfernen möchtest?
                                </v-card-text>
                                <v-card-actions>
                                    <div class="d-flex justify-end w-100">
                                        <v-btn color="error" variant="elevated" class="me-2" @click="isActive.value = false">Abbrechen</v-btn>
                                        <Link
                                            :href="
                                                route('user.destroy', {
                                                    user: item.id,
                                                })
                                            "
                                            method="delete"
                                        >
                                            <v-btn type="submit" color="primary" variant="elevated">Löschen</v-btn>
                                        </Link>
                                    </div>
                                </v-card-actions>
                            </v-card>
                        </template>
                    </v-dialog>
                </template>
            </v-data-table-virtual>
        </v-container>
    </AdminLayout>
</template>
