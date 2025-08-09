import { defineStore } from "pinia";

export const useStore = defineStore("sample-store", {
  state: () => ({
    message: "Hello World",
  }),
  actions: {
    updateMessage(payload: string) {
      this.message = payload;
    },
  },
  getters: {
    getMessage: (state) => state.message,
  },
});
