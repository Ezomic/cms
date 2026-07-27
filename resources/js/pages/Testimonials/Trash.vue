<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import TrashTable from '@/components/TrashTable.vue';
import { ArrowLeft } from 'lucide-vue-next';

interface Row { id: number; quote: string; author_name: string | null; deleted_at: string | null }
const props = defineProps<{ testimonials: Row[] }>();
const items = computed(() => props.testimonials.map((t) => ({
    id: t.id,
    title: t.author_name ?? 'Testimonial',
    subtitle: `“${t.quote.slice(0, 60)}…”`,
    deleted_at: t.deleted_at,
})));
</script>

<template>
    <Head title="Testimonials · Trash" />
    <AdminLayout title="Trash">
        <template #actions><Button as="a" href="/admin/testimonials" variant="outline"><ArrowLeft class="size-4" /> Back</Button></template>
        <TrashTable resource="testimonials" :items="items" label="Testimonial" />
    </AdminLayout>
</template>
