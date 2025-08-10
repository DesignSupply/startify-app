import * as THREE from 'three';
import Stats from 'stats.js';
import GUI from 'lil-gui';
import fragmentShader from '../glsl/main.frag';
import vertexShader from '../glsl/main.vert';

console.log({
  THREE,
  Stats,
  GUI,
});

// webGL class
class WebGL {

  constructor(webGLElement: HTMLElement, options: {
    width: number;
    height: number;
  }) {
    this.init();
  }

  init() {
    console.log('init');
  }

  load() {
    console.log('load');
  }

  render() {
    console.log('render');
  }
}

export default WebGL;
