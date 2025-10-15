import '@/styles/tailwind.css';
import '@/styles/style.scss';
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
  // eslint-disable-next-line no-console
  console.log('jQuery is ready.');
});

// Vue.js
import('./vue');

// React
import('./react');

// WebGL(Three.js)
import WebGL from './three';
window.addEventListener(
  'DOMContentLoaded',
  async () => {
    const webGLElement = document.querySelector<HTMLElement>('#app-three');
    if (!webGLElement) return;
    const webGLInstance = new WebGL(webGLElement, {
      width: webGLElement.clientWidth,
      height: webGLElement.clientHeight
    });
    webGLInstance.initialize();
    webGLInstance.attach();
    await webGLInstance.load();
    webGLInstance.start();
  },
  false
);
