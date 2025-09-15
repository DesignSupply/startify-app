<script lang="ts" setup>
import { reactive, onMounted, inject } from 'vue';
import ExampleComponent from '@/vue/components/ExampleComponent.vue';
import { useStoreKey } from '@/types/vue-store';
import type { storeType } from '@/types/vue-store';
const store = inject<storeType>(useStoreKey);
store?.updateMessage('state updated.');
const data = reactive({
  text: 'This is ExampleComponent (Vue.js)'
});
onMounted(() => {
  // eslint-disable-next-line no-console
  console.log(`Vue.js is ready. ${store?.message}`);
});
</script>

<template>
  <router-view />
  <example-component :message="data.text" />
</template>
