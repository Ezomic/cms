<script setup lang="ts">
import { cn } from '@/lib/utils';
const props = defineProps<{ class?: string }>();
</script>

<template>
    <div :class="cn('flex items-center px-5 py-4 border-t border-border', props.class)">
        <slot />
    </div>
</template>
