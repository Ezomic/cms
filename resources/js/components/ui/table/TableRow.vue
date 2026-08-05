<script setup lang="ts">
import { cn } from '@/lib/utils';
const props = defineProps<{ class?: string }>();
</script>

<template>
    <tr :class="cn('border-border hover:bg-muted/50 border-b transition-colors', props.class)">
        <slot />
    </tr>
</template>
