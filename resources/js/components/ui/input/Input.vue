<script setup lang="ts">
import { cn } from '@/lib/utils';
const props = defineProps<{ class?: string; type?: string; placeholder?: string; id?: string }>();
const model = defineModel<string | number | null>();
</script>

<template>
    <input
        :id="id"
        v-model="model"
        :type="type ?? 'text'"
        :placeholder="placeholder"
        :class="
            cn(
                'border-input bg-card placeholder:text-muted-foreground focus-visible:border-primary focus-visible:ring-ring/30 flex h-9 w-full rounded-md border px-3 py-1 text-sm transition-colors focus-visible:ring-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                props.class,
            )
        "
    />
</template>
