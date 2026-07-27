<script setup lang="ts">
import { cn } from '@/lib/utils';
const props = defineProps<{ class?: string; placeholder?: string; id?: string; rows?: number }>();
const model = defineModel<string | null>();
</script>

<template>
    <textarea
        :id="id"
        :rows="rows ?? 4"
        :placeholder="placeholder"
        v-model="model"
        :class="cn('flex w-full rounded-md border border-input bg-card px-3 py-2 text-sm transition-colors placeholder:text-muted-foreground focus-visible:border-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/30 disabled:opacity-50', props.class)"
    />
</template>
