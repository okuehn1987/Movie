<script setup lang="ts">
import ConfirmDelete from '@/Components/ConfirmDelete.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Group, RelationPick, User, UserAppends } from '@/types/types';
import { useMaxScrollHeight } from '@/utils';
import GroupDialogForm from './partials/GroupDialogForm.vue';
import { ref } from 'vue';
defineProps<{
    groups: (Group & RelationPick<'group', 'users', 'id' | 'first_name' | 'last_name'>)[];
    users: (Pick<User, 'id' | 'first_name' | 'last_name'> & UserAppends)[];
}>();

const tableHeight = useMaxScrollHeight(0);

const search = ref('');
</script>
<template>
    <AdminLayout title="Abteilungen">
        <v-card>
            <v-data-table-virtual
                :search="search"
                :style="{ maxHeight: tableHeight }"
                fixed-header
                :headers="[
                    { title: 'Abteilungsname', key: 'name' },
                    { title: 'Mitarbeitende', key: 'users_count' },
                    { title: '', key: 'actions', align: 'end', width: '350px', sortable: false },
                ]"
                :items="groups.map(g => ({ ...g, users_count: g.users.length }))"
            >
                <template v-slot:header.actions>
                    <div class="d-flex ga-2">
                        <v-text-field
                            autocomplete="off"
                            hide-details
                            density="compact"
                            v-model="search"
                            label="Suchen"
                            variant="outlined"
                        ></v-text-field>
                        <GroupDialogForm :users></GroupDialogForm>
                    </div>
                </template>
                <template v-slot:item.actions="{ item }">
                    <GroupDialogForm v-if="can('group', 'update')" :group="item" :users></GroupDialogForm>
                    <ConfirmDelete
                        v-if="can('group', 'delete')"
                        :route="route('group.destroy', { group: item.id })"
                        :content="`Bist du dir sicher das du die Abteilung '${item.name}' löschen möchtest?`"
                        :title="`Abeilung ${item.name} löschen`"
                    ></ConfirmDelete>
                </template>
            </v-data-table-virtual>
        </v-card>
    </AdminLayout>
</template>
