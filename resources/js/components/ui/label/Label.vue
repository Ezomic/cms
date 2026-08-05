<script setup lang="ts">
import { cn } from '@/lib/utils';
const props = defineProps<{ class?: string; for?: string }>();
</script>

<template>
    <label :for="props.for" :class="cn('text-muted-foreground mb-1.5 block font-mono text-[11px] tracking-[.03em] uppercase', props.class)">
        <slot />
    </label>
</template>
