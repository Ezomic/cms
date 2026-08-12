<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { paginationLabel } from '@/lib/pagination';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Table, TableHeader, TableRow, TableHead, TableCell } from '@/components/ui/table';
import { Plus, GripVertical, Search } from 'lucide-vue-next';

interface ProjectRow {
    id: number;
    name: string;
    slug: string;
    client_name: string | null;
    year: string | null;
    published: boolean;
    featured: boolean;
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
watch(
    () => props.projects,
    (p) => (rows.value = [...p.data]),
);

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
                <span class="bg-accent font-display text-accent-foreground rounded-md px-3 py-1.5 text-sm font-semibold"
                    >All <span class="opacity-60">{{ projects.total }}</span></span
                >
                <Link
                    href="/admin/projects/trash"
                    class="font-display text-muted-foreground hover:bg-muted rounded-md px-3 py-1.5 text-sm font-semibold"
                    >Trash <span class="opacity-60">{{ trashCount }}</span></Link
                >
            </div>
            <div class="relative w-64 max-w-full">
                <Search class="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
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
                            <TableCell><GripVertical class="drag text-muted-foreground/60 size-4 cursor-grab" /></TableCell>
                            <TableCell>
                                <img
                                    v-if="row.image_url"
                                    :src="row.image_url"
                                    alt=""
                                    class="border-border h-9 w-14 rounded-md border object-cover"
                                />
                                <div v-else class="border-border bg-accent h-9 w-14 rounded-md border"></div>
                            </TableCell>
                            <TableCell>
                                <div class="font-display text-sm font-semibold">{{ row.name }}</div>
                                <div class="text-muted-foreground font-mono text-[11px]">/work/{{ row.slug }}</div>
                            </TableCell>
                            <TableCell
                                ><span class="text-muted-foreground font-mono text-xs">{{
                                    row.tag_list.slice(0, 3).join(' · ')
                                }}</span></TableCell
                            >
                            <TableCell
                                ><span class="text-muted-foreground font-mono text-xs">{{ row.year }}</span></TableCell
                            >
                            <TableCell>
                                <Badge v-if="row.published" variant="success"
                                    ><span class="size-1.5 rounded-full bg-current"></span> Published</Badge
                                >
                                <Badge v-else variant="muted"><span class="size-1.5 rounded-full bg-current"></span> Draft</Badge>
                                <Badge v-if="row.featured" variant="outline" class="ml-1">Featured</Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex justify-end gap-1">
                                    <Button as="a" :href="`/admin/projects/${row.id}/edit`" variant="ghost" size="sm">Edit</Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                                        @click="destroy(row)"
                                        >Delete</Button
                                    >
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </draggable>
            </Table>
            <p v-if="!rows.length" class="text-muted-foreground px-5 py-10 text-center text-sm">No projects found.</p>
        </Card>

        <div v-if="projects.links.length > 3" class="flex flex-wrap gap-1">
            <template v-for="link in projects.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    class="border-border rounded-md border px-3 py-1.5 text-sm"
                    :class="link.active ? 'bg-primary text-primary-foreground border-primary' : 'hover:bg-muted'"
                    >{{ paginationLabel(link.label) }}</Link
                >
                <span v-else class="border-border text-muted-foreground/50 rounded-md border px-3 py-1.5 text-sm">{{
                    paginationLabel(link.label)
                }}</span>
            </template>
        </div>
    </AdminLayout>
</template>
