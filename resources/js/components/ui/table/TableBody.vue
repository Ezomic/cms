<script setup lang="ts">
const props = defineProps<{ class?: string }>();
</script>

<template>
    <tbody :class="props.class">
        <slot />
    </tbody>
</template>
