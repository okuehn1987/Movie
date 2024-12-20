<script setup lang="ts">
import { TimeAccount, TimeAccountSetting } from '@/types/types';
import { usePageIsLoading } from '@/utils';
import { router } from '@inertiajs/vue3';

defineProps<{
    item: Pick<TimeAccount, 'id' | 'user_id' | 'balance' | 'balance_limit' | 'time_account_setting_id' | 'name' | 'deleted_at'> & {
        time_account_setting: TimeAccountSetting;
    };
}>();

const loading = usePageIsLoading();
</script>
<template>
    <v-dialog max-width="1000">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn v-bind="activatorProps" color="error" icon variant="text">
                <v-icon icon="mdi-delete"></v-icon>
            </v-btn>
        </template>
        <template v-slot:default="{ isActive }">
            <v-card :title="`Konto ${item.name} löschen`">
                <template #append>
                    <v-btn icon variant="text" @click.stop="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-card-text>
                    <v-form
                        @submit.prevent="
                            router.delete(route('timeAccount.destroy', { timeAccount: item.id }), {
                                onSuccess: () => {
                                    isActive.value = false;
                                },
                            })
                        "
                    >
                        <v-row>
                            <v-col cols="12">
                                <v-alert color="error" v-if="item.balance == 0">
                                    Sind Sie sich sicher das dieses Konto gelöscht werden soll?
                                </v-alert>
                                <v-alert v-else>Konten können nur gelöscht werden wenn die Stunden auf 0 stehen</v-alert>
                            </v-col>
                            <v-col cols="12" class="text-end" v-if="item.balance == 0">
                                <v-btn type="submit" color="primary" :loading> Speichern </v-btn>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
<style lang="scss" scoped></style>
