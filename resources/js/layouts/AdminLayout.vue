<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { LayoutDashboard, FolderKanban, Quote, ListChecks, Inbox, User, Users, ShieldCheck, Search } from 'lucide-vue-next';
import PortalSwitcher from '@/components/PortalSwitcher.vue';

defineProps<{ title?: string }>();

interface SharedProps {
    appName: string;
    auth: { user: { name: string | null } | null };
    counts: { unread: number };
    flash: { status: string | null };
}

const page = usePage<SharedProps & Record<string, unknown>>();
const shared = computed(() => page.props as unknown as SharedProps);
const url = computed(() => page.url);

const csrf = () => document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

const initials = computed(() => {
    const name = shared.value.auth?.user?.name ?? 'CMS';
    return name
        .split(' ')
        .map((p) => p[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

const content = [
    { label: 'Projects', href: '/admin/projects', match: '/admin/projects', icon: FolderKanban },
    { label: 'Testimonials', href: '/admin/testimonials', match: '/admin/testimonials', icon: Quote },
    { label: 'Skills', href: '/admin/skills', match: '/admin/skills', icon: ListChecks },
];
const settings = [
    { label: 'Profile', href: '/admin/profile', match: '/admin/profile', icon: User },
    { label: 'Users', href: '/admin/users', match: '/admin/users', icon: Users },
    { label: 'Security', href: '/admin/security', match: '/admin/security', icon: ShieldCheck },
];

const isActive = (match: string) => (match === '/admin' ? url.value === '/admin' : url.value.startsWith(match));
</script>

<template>
    <div class="bg-background grid min-h-screen grid-cols-1 md:grid-cols-[224px_1fr]">
        <aside class="border-border bg-card hidden flex-col gap-1 border-r p-3 md:flex">
            <div class="font-display flex items-center gap-2 px-2.5 py-2 pb-3.5 text-[15px] font-bold">
                <span class="bg-primary h-4 w-4 rounded-[5px]"></span>CMS
            </div>

            <a href="/admin" class="nav" :class="isActive('/admin') ? 'nav-on' : ''"> <LayoutDashboard class="size-[17px]" /> Dashboard </a>

            <div class="text-muted-foreground/70 px-2.5 pt-3.5 pb-1.5 font-mono text-[10px] tracking-[.12em] uppercase">Content</div>
            <a v-for="i in content" :key="i.href" :href="i.href" class="nav" :class="isActive(i.match) ? 'nav-on' : ''">
                <component :is="i.icon" class="size-[17px]" /> {{ i.label }}
            </a>

            <div class="text-muted-foreground/70 px-2.5 pt-3.5 pb-1.5 font-mono text-[10px] tracking-[.12em] uppercase">Inbox</div>
            <a href="/admin/contact-submissions" class="nav" :class="isActive('/admin/contact-submissions') ? 'nav-on' : ''">
                <Inbox class="size-[17px]" /> Contact
                <span
                    v-if="shared.counts?.unread"
                    class="bg-accent text-accent-foreground ml-auto rounded-full px-1.5 py-0.5 font-mono text-[11px]"
                    >{{ shared.counts.unread }}</span
                >
            </a>

            <div class="text-muted-foreground/70 px-2.5 pt-3.5 pb-1.5 font-mono text-[10px] tracking-[.12em] uppercase">Settings</div>
            <a v-for="i in settings" :key="i.href" :href="i.href" class="nav" :class="isActive(i.match) ? 'nav-on' : ''">
                <component :is="i.icon" class="size-[17px]" /> {{ i.label }}
            </a>

            <div class="border-border mt-auto flex items-center gap-2.5 border-t pt-3">
                <span
                    class="bg-accent font-display text-accent-foreground flex h-7 w-7 items-center justify-center rounded-lg text-xs font-bold"
                    >{{ initials }}</span
                >
                <div class="min-w-0 text-xs">
                    <div class="truncate font-semibold">{{ shared.auth?.user?.name ?? 'Admin' }}</div>
                    <form method="POST" action="/admin/logout">
                        <input type="hidden" name="_token" :value="csrf()" />
                        <button type="submit" class="text-muted-foreground/70 hover:text-foreground">Sign out</button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex min-w-0 flex-col">
            <header class="border-border bg-card flex h-[60px] items-center justify-between gap-4 border-b px-6">
                <div>
                    <div class="text-muted-foreground font-mono text-xs">Admin</div>
                    <h1 class="font-display text-lg font-bold tracking-tight">{{ title }}</h1>
                </div>
                <div class="flex items-center gap-3">
                    <slot name="actions" />
                    <a
                        href="/"
                        target="_blank"
                        class="text-muted-foreground hover:text-foreground hidden items-center gap-1.5 font-mono text-xs sm:flex"
                    >
                        <Search class="size-4" /> View site
                    </a>
                    <PortalSwitcher />
                </div>
            </header>

            <div
                v-if="shared.flash?.status"
                class="border-success/30 bg-success/10 text-success mx-6 mt-4 rounded-lg border px-4 py-3 text-sm"
            >
                {{ shared.flash.status }}
            </div>

            <main class="flex flex-col gap-5 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
@reference '../../css/app.css';
.nav {
    @apply text-muted-foreground hover:bg-muted hover:text-foreground flex items-center gap-2.5 rounded-[10px] px-2.5 py-2 text-sm font-medium;
}
.nav-on {
    @apply bg-accent text-accent-foreground font-semibold;
}
</style>
