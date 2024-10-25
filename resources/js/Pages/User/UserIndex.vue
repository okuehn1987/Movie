<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Group, OperatingSite, Paginator, User, UserPermission } from '@/types/types';
import { router } from '@inertiajs/vue3';
import UserForm from './UserForm.vue';
import { fillNullishValues, usePagination } from '@/utils';
import { DateTime } from 'luxon';
import { toRefs } from 'vue';

const props = defineProps<{
    users: Paginator<User & { group: Pick<Group, 'id' | 'name'> }>;
    groups: Pick<Group, 'id' | 'name'>[];
    operating_sites: Pick<OperatingSite, 'id' | 'name'>[];
    permissions: UserPermission[];
}>();

const { currentPage, lastPage, data } = usePagination(toRefs(props), 'users');
</script>
<template>
    <AdminLayout title="Mitarbeiter">
        <v-container>
            <v-data-table-virtual
                no-data-text="Es wurden keine Mitarbeiter gefunden"
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
                :items="
                    data.map(u => ({
                        ...fillNullishValues(u),
                        date_of_birth: DateTime.fromFormat(u.date_of_birth, 'yyyy-MM-dd').toFormat('dd.MM.yyyy'),
                    }))
                "
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
                        <v-icon size="large" icon="mdi-eye"></v-icon>
                    </v-btn>
                    <v-dialog max-width="1000">
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
                                        <v-btn color="primary" variant="elevated" class="me-2" @click="isActive.value = false">Abbrechen</v-btn>

                                        <v-btn
                                            @click.stop="
                                                router.delete(
                                                    route('user.destroy', {
                                                        user: item.id,
                                                    }),
                                                    { onSuccess: () => (isActive.value = false) },
                                                )
                                            "
                                            color="error"
                                            variant="elevated"
                                            >Löschen</v-btn
                                        >
                                    </div>
                                </v-card-actions>
                            </v-card>
                        </template>
                    </v-dialog>
                </template>
                <template v-slot:bottom>
                    <v-pagination v-if="lastPage > 1" v-model="currentPage" :length="lastPage"></v-pagination>
                </template>
            </v-data-table-virtual>
        </v-container>
    </AdminLayout>
</template>
