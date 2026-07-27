<script setup lang="ts">
import { cn } from '@/lib/utils';
const props = defineProps<{ class?: string }>();
</script>

<template>
    <h3 :class="cn('font-display text-[15px] font-bold leading-none tracking-tight', props.class)">
        <slot />
    </h3>
</template>
