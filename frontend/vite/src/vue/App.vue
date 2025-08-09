<script lang="ts" setup>
import { reactive } from "vue";
import { useStore } from "../ts/store";
import ExampleComponent from "./components/ExampleComponent.vue";
const store = useStore();
const data = reactive({
  text: "ExampleComponent",
});
store.updateMessage("state updated");
console.log(`Vue.js is ready. ${store.message}`);
</script>

<template>
  <router-view />
  <example-component :message="data.text" />
</template>
