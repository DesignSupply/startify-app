import "../scss/main.scss";
import $ from "jquery";
import React from "react";
import ReactDOM from "react-dom/client";
import App from "../tsx/App";

// React
ReactDOM.createRoot(document.getElementById("app") as HTMLElement).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);

// jQuery
$(function () {
  console.log("jQuery is ready.");
});
