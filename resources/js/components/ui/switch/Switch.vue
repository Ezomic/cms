<script setup lang="ts">
import { cn } from '@/lib/utils';
const props = defineProps<{ class?: string; id?: string }>();
const model = defineModel<boolean>({ default: false });
</script>

<template>
    <button
        :id="id"
        type="button"
        role="switch"
        :aria-checked="model"
        @click="model = !model"
        :class="cn(
            'relative inline-flex h-[22px] w-[38px] shrink-0 cursor-pointer items-center rounded-full transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/40 focus-visible:ring-offset-1',
            model ? 'bg-primary' : 'bg-input',
            props.class,
        )"
    >
        <span :class="cn('pointer-events-none block h-[18px] w-[18px] rounded-full bg-white transition-transform', model ? 'translate-x-[18px]' : 'translate-x-0.5')" />
    </button>
</template>
