<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Group, Paginator, User } from '@/types/types';
import { fillNullishValues, usePagination } from '@/utils';
import { useForm } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { ref, toRefs } from 'vue';

const props = defineProps<{
    groups: Paginator<Pick<Group, 'id' | 'name'>>;
    users: Pick<User, 'id' | 'first_name' | 'last_name' | 'email' | 'staff_number' | 'date_of_birth' | 'group_id'>[];
}>();

const { currentPage, lastPage, data } = usePagination(toRefs(props), 'groups');

const groupForm = useForm({
    name: '',
    users: [],
});

const expanded = ref([]);
</script>
<template>
    <AdminLayout title="Abteilungen">
        <v-card>
            <v-data-table-virtual
                :headers="[
                    { title: '#', key: 'id' },
                    { title: 'Abteilungsname', key: 'name' },
                    { title: '', key: 'data-table-expand', align: 'end' },
                ]"
                :items="data"
                v-model:expanded="expanded"
            >
                <template v-slot:header.data-table-expand>
                    <v-dialog max-width="1000">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn v-bind="activatorProps" color="primary">
                                <v-icon icon="mdi-plus"></v-icon>
                            </v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card title="Abteilung erstellen">
                                <template #append>
                                    <v-btn icon variant="text" @click="isActive.value = false">
                                        <v-icon>mdi-close</v-icon>
                                    </v-btn>
                                </template>
                                <v-card-text>
                                    <v-form
                                        @submit.prevent="
                                            groupForm.post(route('group.store'), {
                                                onSuccess: () => {
                                                    groupForm.reset();
                                                    isActive.value = false;
                                                },
                                            })
                                        "
                                    >
                                        <v-row>
                                            <v-col cols="12" sm="6">
                                                <v-text-field
                                                    v-model="groupForm.name"
                                                    :error-messages="groupForm.errors.name"
                                                    label="Abteilungsname"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12" sm="6">
                                                <v-select
                                                    v-model="groupForm.users"
                                                    :error-messages="groupForm.errors.users"
                                                    :items="users.map(u => ({ ...u, name: u.first_name + ' ' + u.last_name }))"
                                                    item-title="name"
                                                    item-value="id"
                                                    label="Wähle Mitarbeiter aus, die zur Abteilung gehören"
                                                    multiple
                                                ></v-select>
                                            </v-col>
                                            <v-col cols="12" class="text-end">
                                                <v-btn :loading="groupForm.processing" type="submit" color="primary">Erstellen</v-btn>
                                            </v-col>
                                        </v-row>
                                    </v-form>
                                </v-card-text>
                            </v-card>
                        </template>
                    </v-dialog>
                </template>
                <template v-slot:expanded-row="{ columns, item }">
                    <tr>
                        <td :colspan="columns.length" style="padding: 0">
                            <v-data-table-virtual
                                class="bg-grey-lighten-4"
                                :headers="[
                                    { title: '#', key: 'id' },
                                    { title: 'Vorname', key: 'first_name' },
                                    { title: 'Nachname', key: 'last_name' },
                                    { title: 'Email', key: 'email' },
                                    {
                                        title: 'Personalnummer',
                                        key: 'staff_number',
                                    },
                                    { title: 'Geburtsdatum', key: 'date_of_birth' },
                                ]"
                                :items="
                                    users
                                        .filter(user => user.group_id == item.id)
                                        .map(u => ({
                                            ...fillNullishValues(u),
                                            date_of_birth: DateTime.fromSQL(u.date_of_birth).toFormat('dd.MM.yyyy'),
                                        }))
                                "
                            ></v-data-table-virtual>
                        </td>
                    </tr>
                </template>
                <template v-slot:bottom>
                    <v-pagination v-if="lastPage > 1" v-model="currentPage" :length="lastPage"></v-pagination>
                </template>
            </v-data-table-virtual>
        </v-card>
    </AdminLayout>
</template>
