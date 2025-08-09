<script lang="ts" setup>
const props = defineProps({
  message: String
});
</script>

<template>
  <p>{{ props.message }}</p>
</template>
