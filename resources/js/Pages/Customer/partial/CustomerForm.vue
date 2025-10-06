<script setup lang="ts">
import { computed } from 'vue';
import { Customer } from '@/types/types';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    customer?: Customer;
}>();

const mode = computed(() => (props.customer ? 'edit' : 'create'));

const emit = defineEmits<{
    close: [];
}>();
const customerForm = useForm({
    name: props.customer?.name ?? '',
    email: props.customer?.email ?? '',
    phone: props.customer?.phone ?? '',
    reference_number: props.customer?.reference_number ?? '',
});
</script>
<template>
    <v-form
        @submit.prevent="
            props.customer?.id
                ? customerForm.patch(route('customer.update', { customer: props.customer.id }))
                : customerForm.post(route('customer.store'))
        "
    >
        <v-card :title="mode == 'create' ? 'Neuen Kunden anlegen' : ''">
            <template #append>
                <v-btn icon variant="text" v-if="mode == 'create'" @click="emit('close')">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </template>
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="6">
                        <v-text-field
                            label="Name"
                            v-model="customerForm.name"
                            :error-messages="customerForm.errors.name"
                            :disabled="!can('customer', 'update')"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            label="E-Mail (optional)"
                            v-model="customerForm.email"
                            :error-messages="customerForm.errors.email"
                            :disabled="!can('customer', 'update')"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            label="Telefonnummer (optional)"
                            v-model="customerForm.phone"
                            :error-messages="customerForm.errors.phone"
                            :disabled="!can('customer', 'update')"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            label="Kundennummer (optional)"
                            v-model="customerForm.reference_number"
                            :error-messages="customerForm.errors.reference_number"
                            :disabled="!can('customer', 'update')"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12">
                        <div class="d-flex justify-space-between">
                            <v-dialog max-width="1000" v-if="customer && can('customer', 'delete')">
                                <template v-slot:activator="{ props: activatorProps }">
                                    <v-btn v-bind="activatorProps" color="error">Löschen</v-btn>
                                </template>

                                <template v-slot:default="{ isActive }">
                                    <v-card title="Kunde Löschen">
                                        <template #append>
                                            <v-btn icon variant="text" @click.stop="isActive.value = false">
                                                <v-icon>mdi-close</v-icon>
                                            </v-btn>
                                        </template>
                                        <v-card-text>
                                            <v-row>
                                                <v-col cols="12">
                                                    Sind Sie sich sicher, dass der Kunde "{{ customer.name }}" gelöscht werden soll?
                                                </v-col>
                                                <v-col cols="12" class="text-end">
                                                    <v-btn
                                                        @click.stop="
                                                            router.delete(route('customer.destroy', { customer: customer.id }), {
                                                                onSuccess: () => (isActive.value = false),
                                                            })
                                                        "
                                                        color="error"
                                                    >
                                                        Löschen
                                                    </v-btn>
                                                </v-col>
                                            </v-row>
                                        </v-card-text>
                                    </v-card>
                                </template>
                            </v-dialog>
                            <div v-else></div>
                            <v-btn color="primary" type="submit" v-if="can('customer', 'update')">speichern</v-btn>
                        </div>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>
    </v-form>
</template>
<style lang="scss" scoped></style>
