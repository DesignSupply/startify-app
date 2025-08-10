import '../styles/tailwind.css';
import '../scss/style.scss';
import $ from 'jquery';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

// GSAP
gsap.registerPlugin(ScrollTrigger);
gsap.fromTo('.js-gsap-animation', {
  opacity: 0,
  y: 32,
}, {
  opacity: 1,
  y: 0,
  duration: 1,
  ease: 'power2.inOut',
  scrollTrigger: {
    trigger: '.js-gsap-animation',
    start: 'top 50%',
    end: 'bottom 50%',
    scrub: 1,
  },
});

// Swiper
const swiper = new Swiper('.swiper', {
  modules: [Navigation, Pagination],
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },
  pagination: {
    el: '.swiper-pagination',
    clickable: true,
  },
});

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
