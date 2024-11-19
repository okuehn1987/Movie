<script setup lang="ts">
import { TimeAccount, TimeAccountSetting } from '@/types/types';
import { useForm } from '@inertiajs/vue3';

defineProps<{
    item: TimeAccount & { time_account_setting: TimeAccountSetting };
}>();

const timeAccountTransactionForm = useForm({
    to_id: null as null | TimeAccount['id'],
    amount: 0,
    description: '',
    signed_value: 1 as 1 | -1,
});
</script>
<template>
    <v-dialog @after-leave="timeAccountTransactionForm.reset()" max-width="1000">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn v-bind="activatorProps" color="primary" icon variant="text" class="me-2">
                <v-icon icon="mdi-pen"></v-icon>
            </v-btn>
        </template>
        <template v-slot:default="{ isActive }">
            <v-card :title="`Stunden für Konto ${item.name} bearbeiten`">
                <v-card-text>
                    <v-form
                        @submit.prevent="
                            timeAccountTransactionForm
                                .transform(data => ({
                                    ...data,
                                    amount: data.signed_value * data.amount,
                                    to_id: item.id,
                                }))
                                .post(route('timeAccountTransaction.store'), {
                                    onSuccess: () => {
                                        isActive.value = false;
                                        timeAccountTransactionForm.reset();
                                    },
                                })
                        "
                    >
                        <v-row
                            ><v-col cols="12" md="6">
                                <v-select
                                    :items="[
                                        { title: 'Stunden hinzufügen', value: 1 },
                                        { title: 'Stunden abziehen', value: -1 },
                                    ]"
                                    label="Vorgang"
                                    v-model="timeAccountTransactionForm.signed_value"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Stunden"
                                    v-model="timeAccountTransactionForm.amount"
                                    :error-messages="timeAccountTransactionForm.errors.amount"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field
                                    label="Beschreibung"
                                    hint="Die Beschreibung ist für die Darstellung der Historie"
                                    v-model="timeAccountTransactionForm.description"
                                    :error-messages="timeAccountTransactionForm.errors.description"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" class="text-end">
                                <v-btn type="submit" color="primary" :loading="timeAccountTransactionForm.processing"> Speichern </v-btn>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
<style lang="scss" scoped></style>
