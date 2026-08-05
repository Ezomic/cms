<script setup lang="ts">
import { cn } from '@/lib/utils';
const props = defineProps<{ class?: string }>();
</script>

<template>
    <th
        :class="
            cn(
                'text-muted-foreground px-3.5 py-3 text-left align-middle font-mono text-[10.5px] font-medium tracking-[.08em] uppercase',
                props.class,
            )
        "
    >
        <slot />
    </th>
</template>
