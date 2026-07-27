<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import TrashTable from '@/components/TrashTable.vue';
import { ArrowLeft } from 'lucide-vue-next';

interface Row { id: number; name: string; category: string; deleted_at: string | null }
const props = defineProps<{ skills: Row[] }>();
const items = computed(() => props.skills.map((s) => ({ id: s.id, title: s.name, subtitle: s.category, deleted_at: s.deleted_at })));
</script>

<template>
    <Head title="Skills · Trash" />
    <AdminLayout title="Trash">
        <template #actions><Button as="a" href="/admin/skills" variant="outline"><ArrowLeft class="size-4" /> Back</Button></template>
        <TrashTable resource="skills" :items="items" label="Skill" />
    </AdminLayout>
</template>
