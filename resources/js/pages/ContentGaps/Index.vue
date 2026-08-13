<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface Row {
    type: string;
    label: string;
    edit_url: string;
    gaps: string[];
}

defineProps<{ rows: Row[] }>();
</script>

<template>
    <Head title="Content gaps" />
    <AdminLayout title="Content gaps">
        <Card>
            <CardHeader>
                <CardTitle>What is missing</CardTitle>
                <span class="text-muted-foreground font-mono text-[11px]">published content only</span>
            </CardHeader>
            <CardContent class="p-0">
                <p class="text-muted-foreground border-border border-b px-5 py-3 text-xs">
                    Dutch fields fall back to English and SEO fields fall back to a description, so these gaps are invisible on the site
                    itself. Drafts are not checked.
                </p>
                <div v-if="rows.length" class="flex flex-col">
                    <div
                        v-for="(row, i) in rows"
                        :key="`${row.type}-${row.label}-${i}`"
                        class="flex flex-wrap items-start justify-between gap-3 px-5 py-3.5"
                        :class="i % 2 ? 'bg-muted/40' : ''"
                    >
                        <div class="min-w-0">
                            <Link :href="row.edit_url" class="font-display text-sm font-semibold hover:underline">{{ row.label }}</Link>
                            <span class="text-muted-foreground ml-2 font-mono text-[11px]">{{ row.type }}</span>
                            <div class="mt-1.5 flex flex-wrap gap-1">
                                <Badge v-for="gap in row.gaps" :key="gap" variant="muted">{{ gap }}</Badge>
                            </div>
                        </div>
                        <Link :href="row.edit_url" class="text-primary shrink-0 font-mono text-xs hover:underline">Fix →</Link>
                    </div>
                </div>
                <p v-else class="text-muted-foreground px-5 py-10 text-center text-sm">
                    Nothing missing. Every published record has its translations, SEO fields and alt text.
                </p>
            </CardContent>
        </Card>
    </AdminLayout>
</template>
