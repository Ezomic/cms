<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { paginationLabel } from '@/lib/pagination';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table';
import { Plus, Search, Star } from 'lucide-vue-next';

interface Row {
    id: number;
    quote: string;
    author_name: string | null;
    author_role: string | null;
    company_name: string | null;
    featured: boolean;
}
interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

const props = defineProps<{ testimonials: Paginator<Row>; filters: { search: string }; trashCount: number }>();

const search = ref(props.filters.search);
let t: ReturnType<typeof setTimeout>;
watch(search, (v) => {
    clearTimeout(t);
    t = setTimeout(
        () => router.get('/admin/testimonials', { search: v || undefined }, { preserveState: true, replace: true, preserveScroll: true }),
        300,
    );
});

const destroy = (row: Row) => {
    if (confirm('Move this testimonial to trash?')) router.delete(`/admin/testimonials/${row.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Testimonials" />
    <AdminLayout title="Testimonials">
        <template #actions>
            <Button as="a" href="/admin/testimonials/create"><Plus class="size-4" /> New testimonial</Button>
        </template>

        <div class="flex items-center justify-between gap-3">
            <div class="flex gap-1">
                <span class="bg-accent font-display text-accent-foreground rounded-md px-3 py-1.5 text-sm font-semibold"
                    >All <span class="opacity-60">{{ testimonials.total }}</span></span
                >
                <Link
                    href="/admin/testimonials/trash"
                    class="font-display text-muted-foreground hover:bg-muted rounded-md px-3 py-1.5 text-sm font-semibold"
                    >Trash <span class="opacity-60">{{ trashCount }}</span></Link
                >
            </div>
            <div class="relative w-64 max-w-full">
                <Search class="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                <Input v-model="search" placeholder="Filter testimonials…" class="pl-9" />
            </div>
        </div>

        <Card>
            <Table>
                <TableHeader>
                    <TableRow class="hover:bg-transparent">
                        <TableHead>Quote</TableHead>
                        <TableHead>Author</TableHead>
                        <TableHead>Featured</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="row in testimonials.data" :key="row.id">
                        <TableCell class="max-w-sm"
                            ><span class="line-clamp-2 text-sm">“{{ row.quote }}”</span></TableCell
                        >
                        <TableCell>
                            <div class="font-display text-sm font-semibold">{{ row.author_name }}</div>
                            <div class="text-muted-foreground font-mono text-[11px]">
                                {{ [row.author_role, row.company_name].filter(Boolean).join(' · ') }}
                            </div>
                        </TableCell>
                        <TableCell>
                            <Badge v-if="row.featured"><Star class="size-3" /> Featured</Badge>
                            <span v-else class="text-muted-foreground font-mono text-xs">—</span>
                        </TableCell>
                        <TableCell>
                            <div class="flex justify-end gap-1">
                                <Button as="a" :href="`/admin/testimonials/${row.id}/edit`" variant="ghost" size="sm">Edit</Button>
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
                </TableBody>
            </Table>
            <p v-if="!testimonials.data.length" class="text-muted-foreground px-5 py-10 text-center text-sm">No testimonials found.</p>
        </Card>

        <div v-if="testimonials.links.length > 3" class="flex flex-wrap gap-1">
            <template v-for="link in testimonials.links" :key="link.label">
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
