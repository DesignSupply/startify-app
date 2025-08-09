<script lang="ts" setup>
import { useRoute } from "vue-router";
const route = useRoute();
</script>

<template>
  <h1>Current page：{{ route.name }}</h1>
</template>
