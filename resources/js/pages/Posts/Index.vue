<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table';
import { Plus, Search } from 'lucide-vue-next';

interface Row { id: number; title: string; slug: string; published: boolean; published_at: string | null }
interface Paginator<T> { data: T[]; links: { url: string | null; label: string; active: boolean }[]; total: number }

const props = defineProps<{ posts: Paginator<Row>; filters: { search: string }; trashCount: number }>();

const search = ref(props.filters.search);
let t: ReturnType<typeof setTimeout>;
watch(search, (v) => { clearTimeout(t); t = setTimeout(() => router.get('/admin/posts', { search: v || undefined }, { preserveState: true, replace: true, preserveScroll: true }), 300); });

const destroy = (row: Row) => { if (confirm(`Move "${row.title}" to trash?`)) router.delete(`/admin/posts/${row.id}`, { preserveScroll: true }); };
</script>

<template>
    <Head title="Posts" />
    <AdminLayout title="Posts">
        <template #actions><Button as="a" href="/admin/posts/create"><Plus class="size-4" /> New post</Button></template>

        <div class="flex items-center justify-between gap-3">
            <div class="flex gap-1">
                <span class="rounded-md bg-accent px-3 py-1.5 font-display text-sm font-semibold text-accent-foreground">All <span class="opacity-60">{{ posts.total }}</span></span>
                <Link href="/admin/posts/trash" class="rounded-md px-3 py-1.5 font-display text-sm font-semibold text-muted-foreground hover:bg-muted">Trash <span class="opacity-60">{{ trashCount }}</span></Link>
            </div>
            <div class="relative w-64 max-w-full">
                <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input v-model="search" placeholder="Filter posts…" class="pl-9" />
            </div>
        </div>

        <Card>
            <Table>
                <TableHeader>
                    <TableRow class="hover:bg-transparent">
                        <TableHead>Title</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Published</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="row in posts.data" :key="row.id">
                        <TableCell>
                            <div class="font-display text-sm font-semibold">{{ row.title }}</div>
                            <div class="font-mono text-[11px] text-muted-foreground">/blog/{{ row.slug }}</div>
                        </TableCell>
                        <TableCell>
                            <Badge v-if="row.published" variant="success"><span class="size-1.5 rounded-full bg-current"></span> Published</Badge>
                            <Badge v-else variant="muted"><span class="size-1.5 rounded-full bg-current"></span> Draft</Badge>
                        </TableCell>
                        <TableCell><span class="font-mono text-xs text-muted-foreground">{{ row.published_at ?? '—' }}</span></TableCell>
                        <TableCell>
                            <div class="flex justify-end gap-1">
                                <Button as="a" :href="`/admin/posts/${row.id}/edit`" variant="ghost" size="sm">Edit</Button>
                                <Button variant="ghost" size="sm" class="text-destructive hover:bg-destructive/10 hover:text-destructive" @click="destroy(row)">Delete</Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
            <p v-if="!posts.data.length" class="px-5 py-10 text-center text-sm text-muted-foreground">No posts found.</p>
        </Card>

        <div v-if="posts.links.length > 3" class="flex flex-wrap gap-1">
            <template v-for="link in posts.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" preserve-scroll class="rounded-md border border-border px-3 py-1.5 text-sm" :class="link.active ? 'bg-primary text-primary-foreground border-primary' : 'hover:bg-muted'" v-html="link.label" />
                <span v-else class="rounded-md border border-border px-3 py-1.5 text-sm text-muted-foreground/50" v-html="link.label" />
            </template>
        </div>
    </AdminLayout>
</template>
