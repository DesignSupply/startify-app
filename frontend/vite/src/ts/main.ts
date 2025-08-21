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
gsap.fromTo(
  '.js-gsap-animation',
  {
    opacity: 0,
    y: 32
  },
  {
    opacity: 1,
    y: 0,
    duration: 1,
    ease: 'power2.inOut',
    scrollTrigger: {
      trigger: '.js-gsap-animation',
      start: 'top 50%',
      end: 'bottom 50%',
      scrub: 1
    }
  }
);

// Swiper
const swiper = new Swiper('.js-swiper', {
  modules: [Navigation, Pagination],
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev'
  },
  pagination: {
    el: '.swiper-pagination',
    clickable: true
  }
});

// jQuery
$(function () {
  console.log('jQuery is ready.');
});

// Vue.js
import('../ts/vue');

// React
import('../tsx/react');

// WebGL(Three.js)
import WebGL from '../ts/three';
window.addEventListener(
  'DOMContentLoaded',
  async () => {
    const webGLElement = document.querySelector<HTMLElement>('#app-three');
    if (!webGLElement) return;
    const webGLInstance = new WebGL(webGLElement, {
      width: webGLElement.clientWidth,
      height: webGLElement.clientHeight
    });
    await webGLInstance.load();
    webGLInstance.render();
  },
  false
);
