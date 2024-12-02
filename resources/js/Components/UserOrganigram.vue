<script setup lang="ts">
import { Tree, User } from '@/types/types';
import { filterTree, mapTree } from '@/utils';
import { computed, ref } from 'vue';

const props = defineProps<{
    currentUser: User;
    users: Tree<Pick<User, 'id' | 'first_name' | 'last_name' | 'supervisor_id' | 'email'>, 'all_supervisees'>[];
    supervisor: Pick<User, 'id' | 'first_name' | 'last_name' | 'email'> | null;
}>();

const opened = ref(props.supervisor ? [props.supervisor.id] : []);

const items = computed(() => {
    const users = filterTree(
        mapTree(props.users, 'all_supervisees', u => ({ name: u.first_name + ' ' + u.last_name, value: u.id })),
        'all_supervisees',
        () => true,
    );
    if (props.supervisor) {
        return [
            {
                name: props.supervisor.first_name + ' ' + props.supervisor.last_name,
                value: props.supervisor.id,
                all_supervisees: users,
            },
        ];
    }
    return users;
});
</script>
<template>
    <VTreeview v-model:opened="opened" :items item-children="all_supervisees">
        <template v-slot:prepend="{ item }">
            <v-icon icon="mdi-account" />
            <v-chip v-if="currentUser.id == item.value">{{ item.name }}</v-chip>
            <span v-else>{{ item.name }}</span>
        </template>
    </VTreeview>
</template>
<style scoped>
:deep(.v-list-group) {
    padding-left: 20px;
    margin-right: 16px;
}
:deep(.v-list-group__items:not(:has(> .v-list-group:last-child > .v-list-group__items[style='display: none;']))) {
    padding-bottom: 16px;
}
:deep(.v-list-group__items) {
    background-color: rgba(var(--v-theme-secondary), 0.1) !important;
}
:deep(.v-list-group__items .v-list-item:last-child) {
    margin-bottom: -16px;
}
:deep(.v-treeview-group.v-list-group .v-list-group__items .v-list-item),
:deep(.v-list-item) {
    padding-inline-start: 0px !important;
}
:deep(.v-list-item__prepend:not(:has(.v-list-item-action))) {
    padding-inline-start: 48px;
}
.v-treeview {
    padding-bottom: 16px;
}
</style>
