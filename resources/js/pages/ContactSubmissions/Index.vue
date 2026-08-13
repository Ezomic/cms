<script setup lang="ts">
import { ref, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { paginationLabel } from '@/lib/pagination';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';

interface Row {
    id: number;
    name: string;
    email: string;
    company: string | null;
    budget: string | null;
    message: string;
    is_read: boolean;
    is_replied: boolean;
    replied_when: string | null;
    note: string | null;
    when: string | null;
}
interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}
const props = defineProps<{
    submissions: Paginator<Row>;
    filters: { state: string };
    counts: { all: number; unread: number; read: number; replied: number };
}>();

const states = [
    { key: 'all', label: 'All' },
    { key: 'unread', label: 'Unread' },
    { key: 'read', label: 'Read' },
    { key: 'replied', label: 'Replied' },
] as const;

const setState = (key: string) => {
    router.get('/admin/contact-submissions', key === 'all' ? {} : { state: key }, { preserveScroll: true });
};

const open = ref<number | null>(null);
const notes = reactive<Record<number, string>>(Object.fromEntries(props.submissions.data.map((r) => [r.id, r.note ?? ''])));

const toggle = (row: Row) => {
    if (open.value === row.id) {
        open.value = null;
        return;
    }
    open.value = row.id;
    if (!row.is_read) router.post(`/admin/contact-submissions/${row.id}/read`, {}, { preserveScroll: true, preserveState: true });
};
const markUnread = (row: Row) => router.post(`/admin/contact-submissions/${row.id}/unread`, {}, { preserveScroll: true });
const toggleReplied = (row: Row) =>
    router.post(`/admin/contact-submissions/${row.id}/${row.is_replied ? 'unreplied' : 'replied'}`, {}, { preserveScroll: true });
const saveNote = (row: Row) =>
    router.post(`/admin/contact-submissions/${row.id}/note`, { note: notes[row.id] ?? '' }, { preserveScroll: true });
const destroy = (row: Row) => {
    if (confirm('Delete this message?')) router.delete(`/admin/contact-submissions/${row.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Contact submissions" />
    <AdminLayout title="Contact submissions">
        <div class="flex flex-wrap gap-1">
            <button
                v-for="s in states"
                :key="s.key"
                type="button"
                class="font-display rounded-md border px-3 py-1.5 text-sm"
                :class="filters.state === s.key ? 'border-primary bg-primary text-primary-foreground' : 'border-border hover:bg-muted'"
                @click="setState(s.key)"
            >
                {{ s.label }} <span class="opacity-60">{{ counts[s.key] }}</span>
            </button>
        </div>

        <Card class="overflow-hidden">
            <div v-for="row in submissions.data" :key="row.id" class="border-border border-b last:border-b-0">
                <button
                    type="button"
                    class="hover:bg-muted/50 flex w-full items-center gap-3 px-5 py-3.5 text-left"
                    :class="!row.is_read ? 'bg-primary/[0.03]' : ''"
                    @click="toggle(row)"
                >
                    <span class="size-2 shrink-0 rounded-full" :class="row.is_read ? 'bg-transparent' : 'bg-primary'"></span>
                    <span class="min-w-0 flex-1">
                        <span class="font-display text-sm" :class="row.is_read ? 'text-muted-foreground font-medium' : 'font-semibold'">{{
                            row.name
                        }}</span>
                        <span class="text-muted-foreground font-mono text-[11px]"> · {{ row.email }}</span>
                        <span class="text-muted-foreground mt-0.5 block truncate text-xs">
                            <template v-if="row.budget">{{ row.budget }} — </template>{{ row.message }}
                        </span>
                    </span>
                    <Badge v-if="row.is_replied" variant="success">Replied</Badge>
                    <span class="text-muted-foreground shrink-0 font-mono text-[11px]">{{ row.when }}</span>
                </button>
                <div v-if="open === row.id" class="border-border bg-muted/30 border-t px-5 py-4">
                    <div class="mb-3 flex flex-wrap gap-2">
                        <Badge variant="outline">{{ row.email }}</Badge>
                        <Badge v-if="row.company" variant="outline">{{ row.company }}</Badge>
                        <Badge v-if="row.budget" variant="muted">Budget: {{ row.budget }}</Badge>
                        <Badge v-if="row.is_replied" variant="success">Replied {{ row.replied_when }}</Badge>
                    </div>
                    <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ row.message }}</p>

                    <div class="mt-4">
                        <label :for="`note-${row.id}`" class="font-display text-xs font-semibold">Internal note</label>
                        <p class="text-muted-foreground mb-1.5 text-xs">Only visible here. Never shown to the sender.</p>
                        <Textarea :id="`note-${row.id}`" v-model="notes[row.id]" :rows="2" placeholder="What came of this?" />
                        <Button variant="outline" size="sm" class="mt-2" @click="saveNote(row)">Save note</Button>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <Button as="a" :href="`mailto:${row.email}`" size="sm">Reply</Button>
                        <Button variant="outline" size="sm" @click="toggleReplied(row)">
                            {{ row.is_replied ? 'Mark not replied' : 'Mark replied' }}
                        </Button>
                        <Button variant="outline" size="sm" @click="markUnread(row)">Mark unread</Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                            @click="destroy(row)"
                            >Delete</Button
                        >
                    </div>
                </div>
            </div>
            <p v-if="!submissions.data.length" class="text-muted-foreground px-5 py-10 text-center text-sm">No messages here.</p>
        </Card>

        <div v-if="submissions.links.length > 3" class="flex flex-wrap gap-1">
            <template v-for="link in submissions.links" :key="link.label">
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
