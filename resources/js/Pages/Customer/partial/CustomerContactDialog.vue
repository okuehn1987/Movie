<script setup lang="ts">
import { Customer, CustomerContact, Relations } from '@/types/types';
import { computed, Ref } from 'vue';

const props = defineProps<{
    customer: Customer & Pick<Relations<'customer'>, 'contacts'>;
    contact?: CustomerContact;
}>();

const mode = computed(() => (props.contact ? 'edit' : 'create'));

const form = useForm({
    name: props.contact?.name ?? '',
    occupation: props.contact?.occupation ?? '',
    email: props.contact?.email ?? '',
    phone_number: props.contact?.phone_number ?? '',
    mobile_number: props.contact?.mobile_number ?? '',
});

function submit(isActive: Ref<boolean>) {
    if (mode.value === 'edit' && props.contact) {
        form.put(route('customerContact.update', { customerContact: props.contact.id }), {
            onSuccess: () => {
                form.reset();
                isActive.value = false;
            },
        });
    } else {
        form.post(route('customer.customerContact.store', { customer: props.customer.id }), {
            onSuccess: () => {
                form.reset();
                isActive.value = false;
            },
        });
    }
}
</script>
<template>
    <v-dialog>
        <template #activator="{ props: activatorProps }">
            <v-btn :variant="mode == 'create' ? 'flat' : 'text'" v-bind="activatorProps" color="primary">
                <v-icon>{{ mode == 'create' ? 'mdi-plus' : 'mdi-pencil' }}</v-icon>
            </v-btn>
        </template>
        <template #default="{ isActive }">
            <v-card title="Kontakt anlegen" @submit.prevent="">
                <template #append>
                    <v-btn icon variant="text" @click.stop="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-divider></v-divider>
                <v-card-text>
                    <v-form @submit.prevent="submit(isActive)">
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field v-model="form.name" label="Name *" :errorMessages="form.errors.name" required></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="form.occupation"
                                    label="Tätigkeit *"
                                    :errorMessages="form.errors.occupation"
                                    required
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field v-model="form.email" label="Email" :errorMessages="form.errors.email"></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="form.phone_number"
                                    label="Telefonnummer"
                                    :errorMessages="form.errors.phone_number"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="form.mobile_number"
                                    label="Mobilnummer"
                                    :errorMessages="form.errors.mobile_number"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" class="text-end">
                                <v-btn type="submit" color="primary">Speichern</v-btn>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
