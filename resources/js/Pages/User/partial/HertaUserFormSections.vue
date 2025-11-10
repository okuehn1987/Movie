<script setup lang="ts">
import { DateTime } from 'luxon';
import { FormData, UserProp } from './userFormTypes';
defineProps<{
    user?: UserProp;
    mode: 'create' | 'edit';
}>();
const userForm = defineModel<ReturnType<typeof useForm<FormData>>>('userForm', { required: true });
</script>

<template>
    <v-card class="mb-4">
        <v-card-item>
            <v-card-title class="mb-2">Wochenarbeitszeit</v-card-title>
        </v-card-item>
        <v-card-text>
            <v-row>
                <v-col cols="12" v-if="userForm.errors.user_working_hours">
                    <v-alert type="error">{{ userForm.errors.user_working_hours }}</v-alert>
                </v-col>
                <v-col cols="12">
                    <v-data-table-virtual
                        :items="userForm.user_working_hours"
                        :headers="[
                            {
                                title: 'Stunden pro Woche',
                                key: 'weekly_working_hours',
                                width: '50%',
                                sortable: false,
                            },
                            {
                                title: 'Aktiv seit',
                                key: 'active_since',
                                sortable: false,
                            },
                            {
                                title: '',
                                key: 'actions',
                                align: 'end',
                                sortable: false,
                            },
                        ]"
                    >
                        <template v-slot:header.actions>
                            <v-btn
                                v-if="!user || can('user', 'update')"
                                color="primary"
                                @click.stop="userForm.user_working_hours.push({ active_since: '', id: null, weekly_working_hours: 0 })"
                            >
                                <v-icon icon="mdi-plus"></v-icon>
                            </v-btn>
                        </template>
                        <template v-slot:item.weekly_working_hours="{ item, index }">
                            <v-text-field
                                data-testid="userWorkingHours-hours"
                                type="number"
                                variant="underlined"
                                v-model="item.weekly_working_hours"
                                :error-messages="userForm.errors[`user_working_hours.${index}.weekly_working_hours`]"
                                :disabled="
                                    (user && !can('user', 'update')) ||
                                    (!!item.active_since && item.active_since < DateTime.now().toFormat('yyyy-MM-dd'))
                                "
                            ></v-text-field>
                        </template>
                        <template v-slot:item.active_since="{ item, index }">
                            <v-text-field
                                data-testid="userWorkingHours-since"
                                type="date"
                                variant="underlined"
                                :min="mode == 'edit' ? DateTime.now().plus({ days: 1 }).toFormat('yyyy-MM-dd') : undefined"
                                v-model="item.active_since"
                                :error-messages="userForm.errors[`user_working_hours.${index}.active_since`]"
                                :disabled="
                                    (user && !can('user', 'update')) ||
                                    (!!item.active_since && item.active_since < DateTime.now().toFormat('yyyy-MM-dd'))
                                "
                            ></v-text-field>
                        </template>
                        <template v-slot:item.actions="{ item, index }">
                            <v-btn
                                color="error"
                                @click.stop="userForm.user_working_hours.splice(index, 1)"
                                v-if="(!user || can('user', 'update')) && (!item.id || item.active_since > DateTime.now().toFormat('yyyy-MM-dd'))"
                            >
                                <v-icon icon="mdi-delete"></v-icon>
                            </v-btn>
                        </template>
                    </v-data-table-virtual>
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>
</template>
