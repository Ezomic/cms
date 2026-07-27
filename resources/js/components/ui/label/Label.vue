<script setup lang="ts">
import { cn } from '@/lib/utils';
const props = defineProps<{ class?: string; for?: string }>();
</script>

<template>
    <label :for="props.for" :class="cn('mb-1.5 block font-mono text-[11px] uppercase tracking-[.03em] text-muted-foreground', props.class)">
        <slot />
    </label>
</template>
