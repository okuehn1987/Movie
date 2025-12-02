<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { FileType } from '@/types/types';

defineProps<{
    file: { id: number; name: string; type: FileType };
    backurl: string;
}>();
</script>
<template>
    <AdminLayout :title="file.name" :backurl="backurl" class="full-page-layout">
        <div class="embed-wrapper">
            <embed
                :src="
                    {
                        ticketRecordFile: route('ticketRecordFile.getContent', { ticketRecordFile: file.id }),
                        ticketFile: route('ticketFile.getContent', { ticketFile: file.id }),
                    }[file.type]
                "
            />
        </div>
    </AdminLayout>
</template>
<style scoped>
.full-page-layout,
.embed-wrapper {
    height: calc(100vh - 64px);
    margin: 0 0 0 -8px;
    width: calc(100% + 16px);
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
