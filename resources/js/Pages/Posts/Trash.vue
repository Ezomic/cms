<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import TrashTable from '@/components/TrashTable.vue';
import { ArrowLeft } from 'lucide-vue-next';

interface Row { id: number; title: string; slug: string; deleted_at: string | null }
const props = defineProps<{ posts: Row[] }>();
const items = computed(() => props.posts.map((p) => ({ id: p.id, title: p.title, subtitle: `/blog/${p.slug}`, deleted_at: p.deleted_at })));
</script>

<template>
    <Head title="Posts · Trash" />
    <AdminLayout title="Trash">
        <template #actions><Button as="a" href="/admin/posts" variant="outline"><ArrowLeft class="size-4" /> Back</Button></template>
        <TrashTable resource="posts" :items="items" label="Post" />
    </AdminLayout>
</template>
