<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ChatFile } from '@/types/types';

defineProps<{
    file: ChatFile;
}>();
</script>
<template>
    <AdminLayout :title="file.name" :backurl="route('isa.index')" class="full-page-layout">
        <div class="embed-wrapper">
            <embed :src="route('file.getContent', { file: file.id })" />
        </div>
    </AdminLayout>
</template>
<style lang="scss" scoped>
.full-page-layout,
.embed-wrapper {
    height: calc(100vh - 64px);
    margin: 0 0 0 -16px;
    width: calc(100% + 24px);
    padding: 0;
    display: flex;
    flex-direction: column;
}

.embed-wrapper {
    flex: 1;
    display: flex;
}

embed {
    flex: 1;
    width: 100%;
    height: 100%;
    border: none;
}
</style>
