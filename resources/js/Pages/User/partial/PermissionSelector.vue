<script setup lang="ts" generic="T extends object">
import { Permission, PermissionValue } from '@/types/types';

defineProps<{
    permissions: { name: Permission[keyof Partial<Permission>]; label: string }[];
    label: string;
    errors: Record<string, string | undefined>;
    objKey: string;
}>();

const permissionObj = defineModel<Partial<Record<Permission[keyof Permission], PermissionValue>>>({ required: true });
</script>
<template>
    <v-col cols="12">
        <v-row>
            <v-col cols="12" sm="6" md="3" v-for="(_, key) in permissionObj" :key="key">
                <v-select
                    :label="permissions.find(p => p.name == key)?.label"
                    :items="[
                        { title: 'Keine Rechte', value: null },
                        { title: 'Lesen', value: 'read' },
                        { title: 'Schreiben', value: 'write' },
                    ]"
                    v-model="permissionObj[key]"
                    :error-messages="
                        Object.entries(errors)
                            .filter(([k, v]) => k.includes(key) && k.includes(objKey) && v)
                            .map(([, v]) => v) as string[]
                    "
                ></v-select>
            </v-col>
        </v-row>
    </v-col>
</template>
<style lang="scss" scoped></style>
