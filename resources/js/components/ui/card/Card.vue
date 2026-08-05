<script setup lang="ts">
import { cn } from '@/lib/utils';
const props = defineProps<{ class?: string }>();
</script>

<template>
    <div :class="cn('border-border bg-card text-card-foreground rounded-xl border', props.class)">
        <slot />
    </div>
</template>
