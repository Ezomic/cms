<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table';

interface Item { id: number; title: string; subtitle?: string | null; deleted_at: string | null }
const props = defineProps<{ resource: string; items: Item[]; label?: string }>();

const restore = (i: Item) => router.post(`/admin/${props.resource}/${i.id}/restore`, {}, { preserveScroll: true });
const forceDelete = (i: Item) => {
    if (confirm(`Permanently delete "${i.title}"? This cannot be undone.`)) {
        router.delete(`/admin/${props.resource}/${i.id}/force`, { preserveScroll: true });
    }
};
</script>

<template>
    <Card>
        <Table>
            <TableHeader>
                <TableRow class="hover:bg-transparent">
                    <TableHead>{{ label ?? 'Item' }}</TableHead>
                    <TableHead>Deleted</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="i in items" :key="i.id">
                    <TableCell>
                        <div class="font-display text-sm font-semibold">{{ i.title }}</div>
                        <div v-if="i.subtitle" class="font-mono text-[11px] text-muted-foreground">{{ i.subtitle }}</div>
                    </TableCell>
                    <TableCell><span class="font-mono text-xs text-muted-foreground">{{ i.deleted_at }}</span></TableCell>
                    <TableCell>
                        <div class="flex justify-end gap-2">
                            <Button variant="outline" size="sm" @click="restore(i)">Restore</Button>
                            <Button variant="ghost" size="sm" class="text-destructive hover:bg-destructive/10 hover:text-destructive" @click="forceDelete(i)">Delete forever</Button>
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
        <p v-if="!items.length" class="px-5 py-10 text-center text-sm text-muted-foreground">Trash is empty.</p>
    </Card>
</template>
