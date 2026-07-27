<script setup lang="ts">
const props = defineProps<{ class?: string }>();
</script>

<template>
    <thead :class="props.class">
        <slot />
    </thead>
</template>
