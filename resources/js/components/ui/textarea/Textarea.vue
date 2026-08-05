<script setup lang="ts">
import { cn } from '@/lib/utils';
const props = defineProps<{ class?: string; placeholder?: string; id?: string; rows?: number }>();
const model = defineModel<string | null>();
</script>

<template>
    <textarea
        :id="id"
        v-model="model"
        :rows="rows ?? 4"
        :placeholder="placeholder"
        :class="
            cn(
                'border-input bg-card placeholder:text-muted-foreground focus-visible:border-primary focus-visible:ring-ring/30 flex w-full rounded-md border px-3 py-2 text-sm transition-colors focus-visible:ring-2 focus-visible:outline-none disabled:opacity-50',
                props.class,
            )
        "
    />
</template>
