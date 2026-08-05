<script setup lang="ts">
import { cn } from '@/lib/utils';
const props = defineProps<{ class?: string }>();
</script>

<template>
    <div :class="cn('border-border flex items-center border-t px-5 py-4', props.class)">
        <slot />
    </div>
</template>
