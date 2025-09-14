<script lang="ts" setup>
import { reactive } from 'vue';
import { useStore } from '@/scripts/store';
import ExampleComponent from '@/vue/components/ExampleComponent.vue';
const store = useStore();
const data = reactive({
  text: 'This is ExampleComponent (Vue.js)'
});
store.updateMessage('state updated.');
// eslint-disable-next-line no-console
console.log(`Vue.js is ready. ${store.message}`);
</script>

<template>
  <router-view />
  <example-component :message="data.text" />
</template>
