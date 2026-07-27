<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { FolderKanban, Quote, ListChecks, Inbox } from 'lucide-vue-next';

interface ActivityItem {
    id: number;
    action: string;
    subject: string;
    label: string | null;
    user: string | null;
    when: string | null;
}

const props = defineProps<{
    projectCount: number;
    skillCount: number;
    testimonialCount: number;
    pageViewCount: number;
    contactCount: number;
    unreadCount: number;
    sparkline: number[];
    topPaths: { path: string; views: number }[];
    activity: ActivityItem[];
}>();

const tiles = computed(() => [
    { label: 'Projects', value: props.projectCount, sub: 'total', icon: FolderKanban },
    { label: 'Testimonials', value: props.testimonialCount, sub: 'total', icon: Quote },
    { label: 'Skills', value: props.skillCount, sub: 'total', icon: ListChecks },
    { label: 'Inbox', value: props.contactCount, sub: props.unreadCount ? `${props.unreadCount} unread` : 'all read', icon: Inbox },
]);

const chart = computed(() => {
    const data = props.sparkline.length ? props.sparkline : [0];
    const max = Math.max(...data, 1);
    const w = 600;
    const h = 150;
    const step = data.length > 1 ? w / (data.length - 1) : w;
    const pts = data.map((v, i) => [i * step, h - (v / max) * (h - 20) - 8] as const);
    const line = pts.map(([x, y], i) => `${i === 0 ? 'M' : 'L'}${x.toFixed(1)},${y.toFixed(1)}`).join(' ');
    const area = `${line} L${w},${h} L0,${h} Z`;
    const last = pts[pts.length - 1];
    return { line, area, last, w, h };
});

const total30 = computed(() => props.sparkline.reduce((a, b) => a + b, 0));
const fmt = (n: number) => n.toLocaleString('en-US');

const pill = (action: string) => {
    if (action === 'created') return 'bg-accent text-accent-foreground';
    if (action === 'deleted') return 'bg-destructive/10 text-destructive';
    return 'bg-muted text-muted-foreground';
};
</script>

<template>
    <Head title="Dashboard" />
    <AdminLayout title="Dashboard">
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <Card v-for="t in tiles" :key="t.label" class="p-4">
                <div class="flex items-center gap-2 font-mono text-[11px] uppercase tracking-wide text-muted-foreground">
                    <component :is="t.icon" class="size-4" /> {{ t.label }}
                </div>
                <div class="mt-2 font-display text-3xl font-bold tabular-nums tracking-tight">{{ fmt(t.value) }}</div>
                <div class="text-xs text-muted-foreground">{{ t.sub }}</div>
            </Card>
        </div>

        <div class="grid gap-5 lg:grid-cols-[1.6fr_1fr]">
            <Card>
                <CardHeader>
                    <CardTitle>Page views</CardTitle>
                    <span class="rounded-full bg-accent px-2.5 py-1 font-mono text-[11px] text-accent-foreground">last 30 days</span>
                </CardHeader>
                <CardContent>
                    <div class="mb-3 flex items-baseline gap-3">
                        <span class="font-display text-2xl font-bold tabular-nums">{{ fmt(total30) }}</span>
                        <span class="font-mono text-xs text-muted-foreground">in the last 30 days · {{ fmt(pageViewCount) }} all-time</span>
                    </div>
                    <svg :viewBox="`0 0 ${chart.w} ${chart.h}`" preserveAspectRatio="none" class="h-36 w-full">
                        <defs>
                            <linearGradient id="fill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0" stop-color="var(--primary)" stop-opacity="0.20" />
                                <stop offset="1" stop-color="var(--primary)" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <path :d="chart.area" fill="url(#fill)" />
                        <path :d="chart.line" fill="none" stroke="var(--primary)" stroke-width="2.5" />
                        <circle :cx="chart.last[0]" :cy="chart.last[1]" r="4" fill="var(--primary)" />
                    </svg>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Top pages</CardTitle>
                    <span class="font-mono text-[11px] text-muted-foreground">all-time</span>
                </CardHeader>
                <CardContent class="p-0">
                    <div v-if="topPaths.length" class="flex flex-col">
                        <div
                            v-for="(p, i) in topPaths"
                            :key="p.path"
                            class="flex items-center justify-between px-5 py-2.5 text-sm"
                            :class="i % 2 ? 'bg-muted/40' : ''"
                        >
                            <span class="truncate font-mono text-muted-foreground">{{ p.path }}</span>
                            <span class="font-mono font-semibold tabular-nums">{{ fmt(p.views) }}</span>
                        </div>
                    </div>
                    <p v-else class="px-5 py-6 text-sm text-muted-foreground">No page views recorded yet.</p>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Recent activity</CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div v-if="activity.length" class="flex flex-col">
                    <div
                        v-for="a in activity"
                        :key="a.id"
                        class="flex flex-wrap items-center gap-3 border-b border-border px-5 py-3 text-sm last:border-b-0"
                    >
                        <span class="rounded-full px-2.5 py-0.5 font-mono text-[11px]" :class="pill(a.action)">{{ a.action }}</span>
                        <span>{{ a.subject }} <b v-if="a.label" class="font-semibold">{{ a.label }}</b></span>
                        <span class="ml-auto font-mono text-[11px] text-muted-foreground">{{ a.user }} · {{ a.when }}</span>
                    </div>
                </div>
                <p v-else class="px-5 py-6 text-sm text-muted-foreground">No activity yet.</p>
            </CardContent>
        </Card>
    </AdminLayout>
</template>
