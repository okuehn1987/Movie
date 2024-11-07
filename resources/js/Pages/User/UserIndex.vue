<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Group, OperatingSite, Paginator, User, UserPermission } from '@/types/types';
import { Link, router } from '@inertiajs/vue3';
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
        <v-card>
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
                        <template v-slot:default="{ isActive }">
                            <UserForm :groups :operating_sites :permissions mode="create">
                                <template #append>
                                    <v-btn icon variant="text" @click="isActive.value = false">
                                        <v-icon>mdi-close</v-icon>
                                    </v-btn>
                                </template>
                            </UserForm>
                        </template>
                    </v-dialog>
                </template>
                <template v-slot:item.actions="{ item }">
                    <div class="d-flex justify-end">
                        <Link
                            :href="
                                route('user.show', {
                                    user: item.id,
                                })
                            "
                        >
                            <v-btn color="primary" size="large" variant="text" icon="mdi-eye" />
                        </Link>
                        <v-dialog max-width="1000">
                            <template v-slot:activator="{ props: activatorProps }">
                                <v-btn v-bind="activatorProps" color="error" size="large" variant="text" icon="mdi-delete" />
                            </template>

                            <template v-slot:default="{ isActive }">
                                <v-card title="Mitarbeiter löschen">
                                    <template #append>
                                        <v-btn icon variant="text" @click="isActive.value = false">
                                            <v-icon>mdi-close</v-icon>
                                        </v-btn>
                                    </template>
                                    <v-card-text>
                                        <v-row>
                                            <v-col cols="12">
                                                Bist du dir sicher, dass du
                                                {{ item.first_name }}
                                                {{ item.last_name }} entfernen möchtest?
                                            </v-col>
                                            <v-col cols="12" class="text-end">
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
                                                    >Löschen</v-btn
                                                >
                                            </v-col>
                                        </v-row>
                                    </v-card-text>
                                </v-card>
                            </template>
                        </v-dialog>
                    </div>
                </template>
                <template v-slot:bottom>
                    <v-pagination v-if="lastPage > 1" v-model="currentPage" :length="lastPage"></v-pagination>
                </template>
            </v-data-table-virtual>
        </v-card>
    </AdminLayout>
</template>
