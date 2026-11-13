<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { paginationLabel } from '@/lib/pagination';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

interface Row {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    note: string | null;
    type: string;
    starts: string;
    ends: string;
    guest_timezone: string;
    locale: string;
    is_cancelled: boolean;
    cancelled_when: string | null;
    is_past: boolean;
    is_read: boolean;
    received: string | null;
}
interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}
defineProps<{
    bookings: Paginator<Row>;
    filters: { state: string };
    totals: { all: number; upcoming: number; past: number; cancelled: number };
}>();

const states = [
    { key: 'all', label: 'All' },
    { key: 'upcoming', label: 'Upcoming' },
    { key: 'past', label: 'Past' },
    { key: 'cancelled', label: 'Cancelled' },
] as const;

const setState = (key: string) => {
    router.get('/admin/bookings', key === 'all' ? {} : { state: key }, { preserveScroll: true });
};

const open = ref<number | null>(null);

const toggle = (row: Row) => {
    if (open.value === row.id) {
        open.value = null;
        return;
    }
    open.value = row.id;
    if (!row.is_read) router.post(`/admin/bookings/${row.id}/read`, {}, { preserveScroll: true, preserveState: true });
};
const markUnread = (row: Row) => router.post(`/admin/bookings/${row.id}/unread`, {}, { preserveScroll: true });

// A valid address may contain "?" or "&", which a mailto link would read as
// extra headers (bcc, body). Encoding them keeps the link to one recipient.
const mailHref = (email: string) => `mailto:${email.replace(/[?&#%]/g, encodeURIComponent)}`;
const telHref = (phone: string) => `tel:${phone.replace(/[^\d+]/g, '')}`;
</script>

<template>
    <Head title="Bookings" />
    <AdminLayout title="Bookings">
        <div class="flex flex-wrap items-center gap-1">
            <button
                v-for="s in states"
                :key="s.key"
                type="button"
                class="font-display rounded-md border px-3 py-1.5 text-sm"
                :class="filters.state === s.key ? 'border-primary bg-primary text-primary-foreground' : 'border-border hover:bg-muted'"
                @click="setState(s.key)"
            >
                {{ s.label }} <span class="opacity-60">{{ totals[s.key] }}</span>
            </button>
            <span class="text-muted-foreground ml-auto text-xs">Read only. Bookings are made and cancelled in Chronos.</span>
        </div>

        <Card class="overflow-hidden">
            <div v-for="row in bookings.data" :key="row.id" class="border-border border-b last:border-b-0">
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
                        <span class="text-muted-foreground mt-0.5 block truncate text-xs" :class="row.is_cancelled ? 'line-through' : ''">
                            {{ row.type }}, {{ row.starts }} to {{ row.ends }}
                        </span>
                    </span>
                    <Badge v-if="row.is_cancelled" variant="destructive">Cancelled</Badge>
                    <Badge v-else-if="row.is_past" variant="muted">Past</Badge>
                    <Badge v-else variant="success">Booked</Badge>
                    <span class="text-muted-foreground shrink-0 font-mono text-[11px]">{{ row.received }}</span>
                </button>
                <div v-if="open === row.id" class="border-border bg-muted/30 border-t px-5 py-4">
                    <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-1.5 text-sm">
                        <dt class="text-muted-foreground">Type</dt>
                        <dd>{{ row.type }}</dd>
                        <dt class="text-muted-foreground">When</dt>
                        <dd>{{ row.starts }} to {{ row.ends }} <span class="text-muted-foreground">(Europe/Amsterdam)</span></dd>
                        <dt class="text-muted-foreground">Status</dt>
                        <dd>{{ row.is_cancelled ? `Cancelled ${row.cancelled_when}` : 'Booked' }}</dd>
                        <dt class="text-muted-foreground">Email</dt>
                        <dd>
                            <a :href="mailHref(row.email)" class="underline underline-offset-2">{{ row.email }}</a>
                        </dd>
                        <dt class="text-muted-foreground">Phone</dt>
                        <dd>
                            <a v-if="row.phone" :href="telHref(row.phone)" class="underline underline-offset-2">{{ row.phone }}</a>
                            <span v-else class="text-muted-foreground">Not given</span>
                        </dd>
                        <dt class="text-muted-foreground">Guest</dt>
                        <dd>{{ row.guest_timezone }}, language {{ row.locale }}</dd>
                        <dt class="text-muted-foreground">Received</dt>
                        <dd>{{ row.received }}</dd>
                    </dl>

                    <p v-if="row.note" class="mt-3 text-sm leading-relaxed whitespace-pre-wrap">{{ row.note }}</p>
                    <p v-else class="text-muted-foreground mt-3 text-sm">No note.</p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <Button variant="outline" size="sm" @click="markUnread(row)">Mark unread</Button>
                    </div>
                </div>
            </div>
            <p v-if="!bookings.data.length" class="text-muted-foreground px-5 py-10 text-center text-sm">No bookings here.</p>
        </Card>

        <div v-if="bookings.links.length > 3" class="flex flex-wrap gap-1">
            <template v-for="link in bookings.links" :key="link.label">
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
