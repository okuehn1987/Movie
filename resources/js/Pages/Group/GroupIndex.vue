<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Count, Group, Paginator, User, UserAppends } from '@/types/types';
import { getMaxScrollHeight, usePagination } from '@/utils';
import { useForm } from '@inertiajs/vue3';
import { toRefs } from 'vue';

const props = defineProps<{
    groups: Paginator<Pick<Group, 'id' | 'name'> & Count<User>>;
    users: (Pick<User, 'id' | 'first_name' | 'last_name'> & UserAppends)[];
}>();

const { currentPage, lastPage, data } = usePagination(toRefs(props), 'groups');

const groupForm = useForm({
    name: '',
    users: [] as User['id'][],
});
</script>
<template>
    <AdminLayout title="Abteilungen">
        <v-card>
            <v-data-table-virtual
                :style="{ maxHeight: getMaxScrollHeight(0) }"
                fixed-header
                :headers="[
                    { title: 'Abteilungsname', key: 'name' },
                    { title: 'Mitarbeitende', key: 'users_count' },
                    { title: '', key: 'actions', align: 'end' },
                ]"
                :items="data"
            >
                <template v-slot:header.actions>
                    <v-dialog max-width="1000" v-if="can('group', 'create')">
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
                                                    data-testid="employeeGroupAssignment"
                                                    v-model="groupForm.users"
                                                    :error-messages="groupForm.errors.users"
                                                    :items="users.map(user => ({ value: user.id, title: user.name }))"
                                                    label="Wähle Mitarbeiter aus, die zur Abteilung gehören (optional)"
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
                <template v-slot:bottom>
                    <v-pagination v-if="lastPage > 1" v-model="currentPage" :length="lastPage"></v-pagination>
                </template>
            </v-data-table-virtual>
        </v-card>
    </AdminLayout>
</template>
