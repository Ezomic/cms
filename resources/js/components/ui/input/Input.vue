<script setup lang="ts">
import { cn } from '@/lib/utils';
const props = defineProps<{ class?: string; type?: string; placeholder?: string; id?: string }>();
const model = defineModel<string | number | null>();
</script>

<template>
    <input
        :id="id"
        :type="type ?? 'text'"
        :placeholder="placeholder"
        v-model="model"
        :class="cn('flex h-9 w-full rounded-md border border-input bg-card px-3 py-1 text-sm transition-colors placeholder:text-muted-foreground focus-visible:border-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/30 disabled:cursor-not-allowed disabled:opacity-50', props.class)"
    >
</template>
