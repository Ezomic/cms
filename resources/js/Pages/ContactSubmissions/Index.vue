<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

interface Row { id: number; name: string; email: string; company: string | null; budget: string | null; message: string; is_read: boolean; when: string | null }
interface Paginator<T> { data: T[]; links: { url: string | null; label: string; active: boolean }[]; total: number }
const props = defineProps<{ submissions: Paginator<Row> }>();

const open = ref<number | null>(null);
const toggle = (row: Row) => {
    if (open.value === row.id) { open.value = null; return; }
    open.value = row.id;
    if (!row.is_read) router.post(`/admin/contact-submissions/${row.id}/read`, {}, { preserveScroll: true, preserveState: true });
};
const markUnread = (row: Row) => router.post(`/admin/contact-submissions/${row.id}/unread`, {}, { preserveScroll: true });
const destroy = (row: Row) => { if (confirm('Delete this message?')) router.delete(`/admin/contact-submissions/${row.id}`, { preserveScroll: true }); };
</script>

<template>
    <Head title="Contact submissions" />
    <AdminLayout title="Contact submissions">
        <Card class="overflow-hidden">
            <div v-for="row in submissions.data" :key="row.id" class="border-b border-border last:border-b-0">
                <button type="button" class="flex w-full items-center gap-3 px-5 py-3.5 text-left hover:bg-muted/50" :class="!row.is_read ? 'bg-primary/[0.03]' : ''" @click="toggle(row)">
                    <span class="size-2 shrink-0 rounded-full" :class="row.is_read ? 'bg-transparent' : 'bg-primary'"></span>
                    <span class="min-w-0 flex-1">
                        <span class="font-display text-sm" :class="row.is_read ? 'font-medium text-muted-foreground' : 'font-semibold'">{{ row.name }}</span>
                        <span class="font-mono text-[11px] text-muted-foreground"> · {{ row.email }}</span>
                        <span class="mt-0.5 block truncate text-xs text-muted-foreground">
                            <template v-if="row.budget">{{ row.budget }} — </template>{{ row.message }}
                        </span>
                    </span>
                    <span class="shrink-0 font-mono text-[11px] text-muted-foreground">{{ row.when }}</span>
                </button>
                <div v-if="open === row.id" class="border-t border-border bg-muted/30 px-5 py-4">
                    <div class="mb-3 flex flex-wrap gap-2">
                        <Badge variant="outline">{{ row.email }}</Badge>
                        <Badge v-if="row.company" variant="outline">{{ row.company }}</Badge>
                        <Badge v-if="row.budget" variant="muted">Budget: {{ row.budget }}</Badge>
                    </div>
                    <p class="whitespace-pre-wrap text-sm leading-relaxed">{{ row.message }}</p>
                    <div class="mt-4 flex gap-2">
                        <Button as="a" :href="`mailto:${row.email}`" size="sm">Reply</Button>
                        <Button variant="outline" size="sm" @click="markUnread(row)">Mark unread</Button>
                        <Button variant="ghost" size="sm" class="text-destructive hover:bg-destructive/10 hover:text-destructive" @click="destroy(row)">Delete</Button>
                    </div>
                </div>
            </div>
            <p v-if="!submissions.data.length" class="px-5 py-10 text-center text-sm text-muted-foreground">No messages yet.</p>
        </Card>

        <div v-if="submissions.links.length > 3" class="flex flex-wrap gap-1">
            <template v-for="link in submissions.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" preserve-scroll class="rounded-md border border-border px-3 py-1.5 text-sm" :class="link.active ? 'bg-primary text-primary-foreground border-primary' : 'hover:bg-muted'" v-html="link.label" />
                <span v-else class="rounded-md border border-border px-3 py-1.5 text-sm text-muted-foreground/50" v-html="link.label" />
            </template>
        </div>
    </AdminLayout>
</template>
