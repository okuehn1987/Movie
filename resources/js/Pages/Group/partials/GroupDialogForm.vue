<script setup lang="ts">
import { Group, RelationPick, User, UserAppends } from '@/types/types';
import { computed, ref } from 'vue';

const props = defineProps<{
    group?: Group & RelationPick<'group', 'users', 'id' | 'first_name' | 'last_name'>;
    users: (Pick<User, 'id' | 'first_name' | 'last_name'> & UserAppends)[];
}>();

const mode = computed(() => (props.group ? 'edit' : 'create'));

const open = ref(false);

const groupForm = useForm({
    name: props.group?.name ?? '',
    users: props.group?.users.map(u => u.id) ?? ([] as User['id'][]),
});

function submit() {
    if (!props.group) {
        groupForm.post(route('group.store'), {
            onSuccess: () => {
                groupForm.reset();
                open.value = false;
            },
        });
    } else {
        groupForm.patch(
            route('group.update', {
                group: props.group.id,
            }),
            {
                onSuccess: () => {
                    open.value = false;
                },
            },
        );
    }
}
</script>
<template>
    <v-dialog v-model="open" max-width="1000" v-if="can('group', 'create')">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn v-if="mode == 'create'" v-bind="activatorProps" color="primary">
                <v-icon icon="mdi-plus"></v-icon>
            </v-btn>
            <v-btn v-else v-bind="activatorProps" color="primary" variant="text" icon="mdi-eye"></v-btn>
        </template>

        <template v-slot:default="{ isActive }">
            <v-card :title="'Abteilung' + (mode == 'create' ? 'erstellen' : 'bearbeiten')">
                <template #append>
                    <v-btn
                        icon
                        variant="text"
                        @click="
                            isActive.value = false;
                            groupForm.reset();
                        "
                    >
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-divider></v-divider>
                <v-card-text>
                    <v-form @submit.prevent="submit()">
                        <v-row>
                            <v-col cols="12" sm="6">
                                <v-text-field v-model="groupForm.name" :error-messages="groupForm.errors.name" label="Abteilungsname"></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-select
                                    data-testid="employeeGroupAssignment"
                                    v-model="groupForm.users"
                                    :error-messages="groupForm.errors.users"
                                    :items="users.map(user => ({ value: user.id, title: user.name }))"
                                    label="Wähle Mitarbeiter aus, die zur Abteilung gehören (optional)"
                                    multiple
                                    chips
                                ></v-select>
                            </v-col>
                            <v-col cols="12" class="text-end">
                                <v-btn :loading="groupForm.processing" type="submit" color="primary">Speichern</v-btn>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
