<script setup lang="ts">
import { Flag, Organization } from '@/types/types';

const props = defineProps<{
    flags: Record<Flag, string>;
    organization: Organization;
}>();
const organizationSettingForm = useForm(
    Object.fromEntries((Object.keys(props.flags) as Flag[]).map(k => [k, !!props.organization[k]])) as Record<Flag, boolean>,
);
</script>
<template>
    <v-card>
        <v-card-text>
            <v-form @submit.prevent="organizationSettingForm.patch(route('organization.saveSettings'))" :disabled="!can('organization', 'update')">
                <v-row>
                    <v-col v-for="(label, flag) in flags" :key="flag" cols="12" md="6">
                        <v-checkbox
                            density="compact"
                            v-model="organizationSettingForm[flag]"
                            :label="label"
                            :errorMessages="organizationSettingForm.errors[flag]"
                        ></v-checkbox>
                    </v-col>
                    <v-col cols="12" class="text-end" v-if="can('organization', 'update')">
                        <v-btn type="submit" color="primary">Speichern</v-btn>
                    </v-col>
                </v-row>
            </v-form>
        </v-card-text>
    </v-card>
</template>
