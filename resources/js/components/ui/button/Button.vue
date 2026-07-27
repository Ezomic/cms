<script setup lang="ts">
import { cn } from '@/lib/utils';
import { buttonVariants, type ButtonVariants } from '.';

const props = withDefaults(
    defineProps<{
        variant?: ButtonVariants['variant'];
        size?: ButtonVariants['size'];
        as?: string;
        class?: string;
    }>(),
    { as: 'button' },
);
</script>

<template>
    <component :is="as" :class="cn(buttonVariants({ variant, size }), props.class)">
        <slot />
    </component>
</template>
