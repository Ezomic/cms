<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table';
import { Plus, GripVertical, Search } from 'lucide-vue-next';

interface Row { id: number; name: string; category: string }
const props = defineProps<{ skills: Row[]; filters: { search: string }; trashCount: number }>();

const rows = ref<Row[]>([...props.skills]);
watch(() => props.skills, (s) => (rows.value = [...s]));

const search = ref(props.filters.search);
let t: ReturnType<typeof setTimeout>;
watch(search, (v) => { clearTimeout(t); t = setTimeout(() => router.get('/admin/skills', { search: v || undefined }, { preserveState: true, replace: true, preserveScroll: true }), 300); });

const onReorder = () => router.post('/admin/skills/reorder', { ids: rows.value.map((r) => r.id) }, { preserveScroll: true, preserveState: true });
const destroy = (row: Row) => { if (confirm(`Move "${row.name}" to trash?`)) router.delete(`/admin/skills/${row.id}`, { preserveScroll: true }); };
</script>

<template>
    <Head title="Skills" />
    <AdminLayout title="Skills">
        <template #actions>
            <Button as="a" href="/admin/skills/create"><Plus class="size-4" /> New skill</Button>
        </template>

        <div class="flex items-center justify-between gap-3">
            <div class="flex gap-1">
                <span class="rounded-md bg-accent px-3 py-1.5 font-display text-sm font-semibold text-accent-foreground">All <span class="opacity-60">{{ skills.length }}</span></span>
                <Link href="/admin/skills/trash" class="rounded-md px-3 py-1.5 font-display text-sm font-semibold text-muted-foreground hover:bg-muted">Trash <span class="opacity-60">{{ trashCount }}</span></Link>
            </div>
            <div class="relative w-64 max-w-full">
                <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input v-model="search" placeholder="Filter skills…" class="pl-9" />
            </div>
        </div>

        <Card>
            <Table>
                <TableHeader>
                    <TableRow class="hover:bg-transparent">
                        <TableHead class="w-8"></TableHead>
                        <TableHead>Skill</TableHead>
                        <TableHead>Category</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <draggable v-model="rows" tag="tbody" item-key="id" handle=".drag" @end="onReorder">
                    <template #item="{ element: row }">
                        <TableRow>
                            <TableCell><GripVertical class="drag size-4 cursor-grab text-muted-foreground/60" /></TableCell>
                            <TableCell><span class="font-display text-sm font-semibold">{{ row.name }}</span></TableCell>
                            <TableCell><span class="font-mono text-xs text-muted-foreground">{{ row.category }}</span></TableCell>
                            <TableCell>
                                <div class="flex justify-end gap-1">
                                    <Button as="a" :href="`/admin/skills/${row.id}/edit`" variant="ghost" size="sm">Edit</Button>
                                    <Button variant="ghost" size="sm" class="text-destructive hover:bg-destructive/10 hover:text-destructive" @click="destroy(row)">Delete</Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </draggable>
            </Table>
            <p v-if="!rows.length" class="px-5 py-10 text-center text-sm text-muted-foreground">No skills found.</p>
        </Card>
    </AdminLayout>
</template>
