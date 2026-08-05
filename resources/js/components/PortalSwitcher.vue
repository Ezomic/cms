<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { LayoutGrid } from 'lucide-vue-next';
import { DialogRoot, DialogTrigger, DialogPortal, DialogOverlay, DialogContent, DialogTitle, DialogDescription } from 'reka-ui';
import { computed, ref } from 'vue';
import AppIcon from '@/components/AppIcon.vue';

interface PortalApp {
    slug: string;
    name: string;
    initials: string;
    accent: string | null;
    launch_url: string;
    current: boolean;
}

interface PortalCategory {
    category: string;
    apps: PortalApp[];
}

const page = usePage();

const open = ref(false);

const apps = computed<PortalApp[]>(() => (page.props.portalApps as PortalApp[] | undefined) ?? []);

const categories = computed<PortalCategory[]>(() => (page.props.portalCategories as PortalCategory[] | undefined) ?? []);

// The main apps render label-less first, then each category as its own
// labeled section. Empty sections are dropped so no stray heading shows.
const sections = computed(() =>
    [
        { key: '__main__', label: null as string | null, apps: apps.value },
        ...categories.value.map((group) => ({
            key: group.category,
            label: group.category,
            apps: group.apps,
        })),
    ].filter((section) => section.apps.length > 0),
);
</script>

<template>
    <DialogRoot v-model:open="open">
        <DialogTrigger
            class="text-muted-foreground hover:bg-muted hover:text-foreground flex size-8 items-center justify-center rounded-lg"
            aria-label="Switch app"
        >
            <LayoutGrid class="size-4" />
        </DialogTrigger>

        <DialogPortal>
            <DialogOverlay class="fixed inset-0 z-50 bg-black/50" />
            <DialogContent
                class="border-border bg-card fixed top-1/2 left-1/2 z-50 w-[calc(100vw-2rem)] max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl border p-6 shadow-lg focus:outline-none"
            >
                <DialogTitle class="font-display text-lg font-bold tracking-tight"> Your apps </DialogTitle>
                <DialogDescription class="text-muted-foreground mt-1 text-sm"> Jump to another Thijssensoftware app. </DialogDescription>

                <p v-if="sections.length === 0" class="text-muted-foreground py-8 text-center text-sm">No other apps available.</p>

                <div v-else class="mt-4 space-y-4">
                    <div v-for="section in sections" :key="section.key">
                        <p v-if="section.label" class="text-muted-foreground mb-2 text-xs font-medium tracking-wide uppercase">
                            {{ section.label }}
                        </p>

                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <component
                                :is="app.current ? 'div' : 'a'"
                                v-for="app in section.apps"
                                :key="app.slug"
                                :href="app.current ? undefined : app.launch_url"
                                :aria-current="app.current ? 'page' : undefined"
                                class="border-border flex flex-col items-center gap-2 rounded-lg border p-4 text-center transition-colors"
                                :class="app.current ? 'bg-muted/50 cursor-default' : 'hover:border-primary/40 hover:bg-muted'"
                            >
                                <AppIcon :launch-url="app.launch_url" :initials="app.initials" :accent="app.accent" />
                                <span class="text-sm font-medium">{{ app.name }}</span>
                                <span v-if="app.current" class="text-muted-foreground text-[11px]"> Current </span>
                            </component>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
