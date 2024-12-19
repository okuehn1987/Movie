<script setup lang="ts" generic="T extends object">
import { Permission, PermissionValue } from '@/types/types';
import { computed } from 'vue';

const props = defineProps<{
    permissions: { name: Permission[keyof Partial<Permission>]; label: string }[];
    label: string;
    errors: Record<string, string>;
    objKey: string;
}>();

const permissionObj = defineModel<Partial<Record<Permission[keyof Permission], PermissionValue>>>({ required: true });

const selectedPermissions = computed({
    get: () =>
        Object.entries(permissionObj.value)
            .filter(([key, value]) => props.permissions.find(p => p.name == key) && value)
            .map(([key]) => key) as Permission[keyof Permission][],
    set: (value: Permission[keyof Permission][]) => {
        const newPermissions = value.reduce((acc, permission) => {
            acc[permission] = permissionObj.value[permission] || 'read';
            return acc;
        }, {} as Record<Permission[keyof Permission], PermissionValue>);
        permissionObj.value = { ...Object.fromEntries(Object.keys(permissionObj.value).map(k => [k, null])), ...newPermissions };
    },
});
</script>
<template>
    <v-col cols="12">
        <v-select
            data-testid="permissionSelector"
            multiple
            chips
            :label
            v-model="selectedPermissions"
            :items="permissions.filter(p => p.name in permissionObj).map(p => ({ title: p.label, value: p.name }))"
        />
    </v-col>
    <v-col cols="12" md="6" v-for="permission in selectedPermissions" :key="permission">
        <div class="d-flex align-center">
            <span class="w-50">{{ permissions.find(p => p.name == permission)?.label }}</span>
            <span class="w-50">
                <v-select
                    class="ms-4"
                    label="Stufe"
                    :items="[
                        { title: 'Lesen', value: 'read' },
                        { title: 'Schreiben', value: 'write' },
                    ]"
                    v-model="permissionObj[permission]"
                    :error-messages="
                        Object.entries(errors)
                            .filter(([k]) => k.includes(permission) && k.includes(objKey))
                            .map(([, v]) => v)
                    "
                ></v-select>
            </span>
        </div>
    </v-col>
</template>
<style lang="scss" scoped></style>
