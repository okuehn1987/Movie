<script setup lang="ts">
import { Relations, TimeAccount } from '@/types/types';

defineProps<{
    item: Pick<TimeAccount, 'id' | 'user_id' | 'balance' | 'balance_limit' | 'time_account_setting_id' | 'name' | 'deleted_at'> &
        Pick<Relations<'timeAccount'>, 'time_account_setting'>;
}>();

const timeAccountTransactionForm = useForm({
    from_id: null as null | TimeAccount['id'],
    to_id: null as null | TimeAccount['id'],
    amount: 0,
    description: '',
    addOrSub: 'ADD' as 'ADD' | 'SUB',
});
</script>
<template>
    <v-dialog @after-leave="timeAccountTransactionForm.reset()" max-width="1000">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn v-bind="activatorProps" color="primary" icon variant="text">
                <v-icon icon="mdi-plus-minus"></v-icon>
            </v-btn>
        </template>
        <template v-slot:default="{ isActive }">
            <v-card :title="`Stunden für Konto ${item.name} bearbeiten`">
                <template #append>
                    <v-btn icon variant="text" @click.stop="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-card-text>
                    <v-form
                        @submit.prevent="
                            timeAccountTransactionForm
                                .transform(data => ({
                                    ...data,
                                    amount: data.amount,
                                    from_id: data.addOrSub == 'ADD' ? null : item.id,
                                    to_id: data.addOrSub == 'ADD' ? item.id : null,
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
                                        { title: 'Stunden hinzufügen', value: 'ADD' },
                                        { title: 'Stunden abziehen', value: 'SUB' },
                                    ]"
                                    label="Vorgang"
                                    v-model="timeAccountTransactionForm.addOrSub"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    type="number"
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
