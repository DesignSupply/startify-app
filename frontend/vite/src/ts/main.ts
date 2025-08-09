import '../styles/tailwind.css';
import '../scss/style.scss';
import $ from 'jquery';

// jQuery
$(function () {
  console.log('jQuery is ready.');
});

// Vue.js
if (document.querySelector('#app-vue')) {
  import('../ts/vue');
}

// React
if (document.querySelector('#app-react')) {
  import('../tsx/react');
}
