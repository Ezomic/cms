<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table';
import { Plus, GripVertical, Search } from 'lucide-vue-next';

interface ProjectRow {
    id: number;
    name: string;
    slug: string;
    client_name: string | null;
    year: string | null;
    published: boolean;
    tag_list: string[];
    image_url: string | null;
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

const props = defineProps<{
    projects: Paginator<ProjectRow>;
    filters: { search: string };
    trashCount: number;
}>();

const rows = ref<ProjectRow[]>([...props.projects.data]);
watch(() => props.projects, (p) => (rows.value = [...p.data]));

const search = ref(props.filters.search);
let searchTimer: ReturnType<typeof setTimeout>;
watch(search, (value) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get('/admin/projects', { search: value || undefined }, { preserveState: true, replace: true, preserveScroll: true });
    }, 300);
});

const onReorder = () => {
    router.post('/admin/projects/reorder', { ids: rows.value.map((r) => r.id) }, { preserveScroll: true, preserveState: true });
};

const destroy = (row: ProjectRow) => {
    if (confirm(`Move "${row.name}" to trash?`)) {
        router.delete(`/admin/projects/${row.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Projects" />
    <AdminLayout title="Projects">
        <template #actions>
            <Button as="a" href="/admin/projects/create"><Plus class="size-4" /> New project</Button>
        </template>

        <div class="flex items-center justify-between gap-3">
            <div class="flex gap-1">
                <span class="rounded-md bg-accent px-3 py-1.5 font-display text-sm font-semibold text-accent-foreground">All <span class="opacity-60">{{ projects.total }}</span></span>
                <Link href="/admin/projects/trash" class="rounded-md px-3 py-1.5 font-display text-sm font-semibold text-muted-foreground hover:bg-muted">Trash <span class="opacity-60">{{ trashCount }}</span></Link>
            </div>
            <div class="relative w-64 max-w-full">
                <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input v-model="search" placeholder="Filter projects…" class="pl-9" />
            </div>
        </div>

        <Card>
            <Table>
                <TableHeader>
                    <TableRow class="hover:bg-transparent">
                        <TableHead class="w-8"></TableHead>
                        <TableHead class="w-16"></TableHead>
                        <TableHead>Project</TableHead>
                        <TableHead>Tags</TableHead>
                        <TableHead>Year</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <draggable v-model="rows" tag="tbody" item-key="id" handle=".drag" @end="onReorder">
                    <template #item="{ element: row }">
                        <TableRow>
                            <TableCell><GripVertical class="drag size-4 cursor-grab text-muted-foreground/60" /></TableCell>
                            <TableCell>
                                <img v-if="row.image_url" :src="row.image_url" alt="" class="h-9 w-14 rounded-md border border-border object-cover" />
                                <div v-else class="h-9 w-14 rounded-md border border-border bg-accent"></div>
                            </TableCell>
                            <TableCell>
                                <div class="font-display text-sm font-semibold">{{ row.name }}</div>
                                <div class="font-mono text-[11px] text-muted-foreground">/work/{{ row.slug }}</div>
                            </TableCell>
                            <TableCell><span class="font-mono text-xs text-muted-foreground">{{ row.tag_list.slice(0, 3).join(' · ') }}</span></TableCell>
                            <TableCell><span class="font-mono text-xs text-muted-foreground">{{ row.year }}</span></TableCell>
                            <TableCell>
                                <Badge v-if="row.published" variant="success"><span class="size-1.5 rounded-full bg-current"></span> Published</Badge>
                                <Badge v-else variant="muted"><span class="size-1.5 rounded-full bg-current"></span> Draft</Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex justify-end gap-1">
                                    <Button as="a" :href="`/admin/projects/${row.id}/edit`" variant="ghost" size="sm">Edit</Button>
                                    <Button variant="ghost" size="sm" class="text-destructive hover:bg-destructive/10 hover:text-destructive" @click="destroy(row)">Delete</Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </draggable>
            </Table>
            <p v-if="!rows.length" class="px-5 py-10 text-center text-sm text-muted-foreground">No projects found.</p>
        </Card>

        <div v-if="projects.links.length > 3" class="flex flex-wrap gap-1">
            <template v-for="link in projects.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    class="rounded-md border border-border px-3 py-1.5 text-sm"
                    :class="link.active ? 'bg-primary text-primary-foreground border-primary' : 'hover:bg-muted'"
                    v-html="link.label"
                />
                <span v-else class="rounded-md border border-border px-3 py-1.5 text-sm text-muted-foreground/50" v-html="link.label" />
            </template>
        </div>
    </AdminLayout>
</template>
