import "../scss/style.scss";
import $ from "jquery";
import { createApp } from "vue";
import { createPinia } from "pinia";
import { useRouter } from "./route";
import App from "../vue/App.vue";

// Vue.js
createApp(App).use(createPinia()).use(useRouter).mount("#app");

// jQuery
$(function () {
  console.log("jQuery is ready.");
});
