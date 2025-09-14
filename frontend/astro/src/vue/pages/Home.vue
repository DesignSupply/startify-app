<script lang="ts" setup>
import { useRoute } from 'vue-router';
const route = useRoute();
</script>

<template>
  <h1>Current page is {{ route.name }} (Vue Router)</h1>
</template>
