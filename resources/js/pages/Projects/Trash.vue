<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Table, TableHeader, TableRow, TableHead, TableCell } from '@/components/ui/table';
import { ArrowLeft } from 'lucide-vue-next';

interface TrashRow {
    id: number;
    name: string;
    slug: string;
    deleted_at: string | null;
}

defineProps<{ projects: TrashRow[] }>();

const restore = (row: TrashRow) => router.post(`/admin/projects/${row.id}/restore`, {}, { preserveScroll: true });

const forceDelete = (row: TrashRow) => {
    if (confirm(`Permanently delete "${row.name}"? This cannot be undone.`)) {
        router.delete(`/admin/projects/${row.id}/force`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Projects · Trash" />
    <AdminLayout title="Trash">
        <template #actions>
            <Button as="a" href="/admin/projects" variant="outline"><ArrowLeft class="size-4" /> Back to projects</Button>
        </template>

        <Card>
            <Table>
                <TableHeader>
                    <TableRow class="hover:bg-transparent">
                        <TableHead>Project</TableHead>
                        <TableHead>Deleted</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="row in projects" :key="row.id">
                        <TableCell>
                            <div class="font-display text-sm font-semibold">{{ row.name }}</div>
                            <div class="text-muted-foreground font-mono text-[11px]">/work/{{ row.slug }}</div>
                        </TableCell>
                        <TableCell
                            ><span class="text-muted-foreground font-mono text-xs">{{ row.deleted_at }}</span></TableCell
                        >
                        <TableCell>
                            <div class="flex justify-end gap-2">
                                <Button variant="outline" size="sm" @click="restore(row)">Restore</Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                                    @click="forceDelete(row)"
                                    >Delete forever</Button
                                >
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
            <p v-if="!projects.length" class="text-muted-foreground px-5 py-10 text-center text-sm">Trash is empty.</p>
        </Card>
    </AdminLayout>
</template>
