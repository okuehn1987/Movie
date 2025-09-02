<script setup lang="ts">
import { Relations, TimeAccount } from '@/types/types';
defineProps<{
    time_accounts: (Pick<TimeAccount, 'id' | 'user_id' | 'balance' | 'balance_limit' | 'time_account_setting_id' | 'name' | 'deleted_at'> &
        Pick<Relations<'timeAccount'>, 'time_account_setting'>)[];
}>();

const timeAccountTransferForm = useForm({
    from_id: null as null | TimeAccount['id'],
    to_id: null as null | TimeAccount['id'],
    amount: 0,
    description: '',
});
</script>
<template>
    <v-dialog @after-leave="timeAccountTransferForm.reset()" max-width="1000" v-if="time_accounts.length >= 2">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn v-bind="activatorProps" color="secondary" class="me-2">
                <v-icon icon="mdi-transfer"></v-icon>
            </v-btn>
        </template>
        <template v-slot:default="{ isActive }">
            <v-card title="Stundentransaktion durchführen">
                <template #append>
                    <v-btn icon variant="text" @click.stop="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-card-text>
                    <v-form
                        @submit.prevent="
                            timeAccountTransferForm.post(route('timeAccountTransaction.store'), {
                                onSuccess: () => {
                                    isActive.value = false;
                                    timeAccountTransferForm.reset();
                                },
                            })
                        "
                    >
                        <v-row>
                            <v-col cols="12" md="4">
                                <v-select
                                    data-testid="timeAccountTransactionStartAccount"
                                    :items="
                                        time_accounts
                                            .filter(t => t.id != timeAccountTransferForm.to_id && t.deleted_at === null)
                                            .map(t => ({
                                                title: t.name,
                                                value: t.id,
                                            }))
                                    "
                                    label="Von"
                                    v-model="timeAccountTransferForm.from_id"
                                    :error-messages="timeAccountTransferForm.errors.from_id"
                                    clearable
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-select
                                    data-testid="timeAccountTransactionDestinationAccount"
                                    :items="
                                        time_accounts
                                            .filter(t => t.id != timeAccountTransferForm.from_id && t.deleted_at === null)
                                            .map(t => ({
                                                title: t.name,
                                                value: t.id,
                                            }))
                                    "
                                    label="Nach"
                                    v-model="timeAccountTransferForm.to_id"
                                    :error-messages="timeAccountTransferForm.errors.to_id"
                                    clearable
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-text-field
                                    label="Stunden"
                                    v-model="timeAccountTransferForm.amount"
                                    :error-messages="timeAccountTransferForm.errors.amount"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field
                                    label="Beschreibung"
                                    hint="Die Beschreibung ist für die Darstellung der Historie"
                                    v-model="timeAccountTransferForm.description"
                                    :error-messages="timeAccountTransferForm.errors.description"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" class="text-end">
                                <v-btn type="submit" color="primary" :loading="timeAccountTransferForm.processing">Speichern</v-btn>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
<style lang="scss" scoped></style>
