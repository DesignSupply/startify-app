import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';
import { EffectComposer } from 'three/examples/jsm/postprocessing/EffectComposer.js';
import { RenderPass } from 'three/examples/jsm/postprocessing/RenderPass.js';
import { GlitchPass } from 'three/examples/jsm/postprocessing/GlitchPass.js';
import { DotScreenPass } from 'three/examples/jsm/postprocessing/DotScreenPass.js';
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';
import { DRACOLoader } from 'three/examples/jsm/loaders/DRACOLoader.js';
import Stats from 'stats.js';
import GUI from 'lil-gui';
import fragmentShader from '../glsl/main.frag';
import vertexShader from '../glsl/main.vert';
import textureImage from '../images/webgl_texture_image.jpg';

// webGL class
class WebGL {

  static CAMERA_SCALE = 1.0;

  static CAMERA_SETTINGS = {
    perspective: {
      fov: 13, // field of view
      aspect: window.innerWidth / window.innerHeight,
      near: 0.1,
      far: 100,
      position: new THREE.Vector3(6.0, 3.5, 6.0),
      lookAt: new THREE.Vector3(0, 0, 0), // center of the scene
    },
    orthographic: {
      left: (WebGL.CAMERA_SCALE * window.innerWidth / window.innerHeight) * -1,
      right: WebGL.CAMERA_SCALE * window.innerWidth / window.innerHeight,
      top: WebGL.CAMERA_SCALE,
      bottom: WebGL.CAMERA_SCALE * -1,
      near: 0.1,
      far: 100.0,
      position: new THREE.Vector3(10.0, 10.0, 10.0),
      lookAt: new THREE.Vector3(0, 0, 0), // center of the scene
    }
  }

  static RENDERER_SETTINGS = {
    clearColor: 0x666666,
    alpha: 1.0,
    width: window.innerWidth,
    height: window.innerHeight,
    pixelRatio: window.devicePixelRatio,
  }

  static LIGHT_SETTINGS = {
    directionalLight: {
      color: 0xffffff,
      intensity: 1.0,
      position: new THREE.Vector3(-1.0, 1.0, 4.0),
    },
    ambientLight: {
      color: 0xffffff,
      intensity: 0.5,
    },
    spotLight: {
      color: 0xffff00,
      intensity: 50.0,
      distance: 100.0,
      angle: Math.PI / 2,
      penumbra: 0.5,
      decay: 2.0,
      position: new THREE.Vector3(3.0, 3.0, 3.0),
    },
  }

  static MATERIAL_SETTINGS = {
    color: 0x0ae0ce,
    wireframe: false,
    transparent: false,
    // opacity: 0.5,
    side: THREE.FrontSide, // THREE.DoubleSide, THREE.FrontSide, THREE.BackSide
  }

  static AXES_SETTINGS = {
    size: 10.0,
  }

  static FOG_SETTINGS = {
    color: 0xffffff,
    near: 0.1,
    far: 30,
  }

  renderer: THREE.WebGLRenderer;
  scene: THREE.Scene;
  perspectiveCamera: THREE.PerspectiveCamera;
  orthographicCamera: THREE.OrthographicCamera;
  directionalLight: THREE.DirectionalLight;
  ambientLight: THREE.AmbientLight;
  spotLight: THREE.SpotLight;
  material: THREE.MeshPhongMaterial;
  geometry: THREE.BoxGeometry;
  mesh: THREE.Mesh;
  group: THREE.Group;
  spotLightHelper: THREE.SpotLightHelper;
  axesHelper: THREE.AxesHelper;
  textureLoader: THREE.TextureLoader;
  fog: THREE.Fog;
  effectComposer: EffectComposer;
  renderPass: RenderPass;
  glitchPass: GlitchPass;
  dotScreenPass: DotScreenPass;
  controls: OrbitControls;
  gltfLoader: GLTFLoader;
  dracoLoader: DRACOLoader;
  model: THREE.Group;
  raycaster: THREE.Raycaster;
  clock: THREE.Clock;
  stats: Stats;
  gui: GUI;


  constructor(webGLElement: HTMLElement, options: {
    width: number;
    height: number;
  }) {

    // settings override
    WebGL.CAMERA_SETTINGS.perspective.aspect = options.width / options.height;
    WebGL.CAMERA_SETTINGS.orthographic.left = (WebGL.CAMERA_SCALE * options.width / options.height) * -1;
    WebGL.CAMERA_SETTINGS.orthographic.right = WebGL.CAMERA_SCALE * options.width / options.height;
    WebGL.RENDERER_SETTINGS.width = options.width;
    WebGL.RENDERER_SETTINGS.height = options.height;

    // renderer
    this.renderer = new THREE.WebGLRenderer();
    this.renderer.setSize(WebGL.RENDERER_SETTINGS.width, WebGL.RENDERER_SETTINGS.height);
    this.renderer.setPixelRatio(WebGL.RENDERER_SETTINGS.pixelRatio);
    this.renderer.setClearColor(WebGL.RENDERER_SETTINGS.clearColor);
    this.renderer.setClearAlpha(WebGL.RENDERER_SETTINGS.alpha);
    this.renderer.shadowMap.enabled = true;
    webGLElement.appendChild(this.renderer.domElement);

    // perspective camera
    this.perspectiveCamera = new THREE.PerspectiveCamera(
      WebGL.CAMERA_SETTINGS.perspective.fov,
      WebGL.CAMERA_SETTINGS.perspective.aspect,
      WebGL.CAMERA_SETTINGS.perspective.near,
      WebGL.CAMERA_SETTINGS.perspective.far
    );
    this.perspectiveCamera.position.copy(WebGL.CAMERA_SETTINGS.perspective.position);
    this.perspectiveCamera.lookAt(WebGL.CAMERA_SETTINGS.perspective.lookAt);
    
    // orthographic camera
    this.orthographicCamera = new THREE.OrthographicCamera(
      WebGL.CAMERA_SETTINGS.orthographic.left,
      WebGL.CAMERA_SETTINGS.orthographic.right,
      WebGL.CAMERA_SETTINGS.orthographic.top,
      WebGL.CAMERA_SETTINGS.orthographic.bottom,
      WebGL.CAMERA_SETTINGS.orthographic.near,
      WebGL.CAMERA_SETTINGS.orthographic.far
    );
    this.orthographicCamera.position.copy(WebGL.CAMERA_SETTINGS.orthographic.position);
    this.orthographicCamera.lookAt(WebGL.CAMERA_SETTINGS.orthographic.lookAt);

    // directional light
    this.directionalLight = new THREE.DirectionalLight(
      WebGL.LIGHT_SETTINGS.directionalLight.color,
      WebGL.LIGHT_SETTINGS.directionalLight.intensity
    );
    this.directionalLight.position.copy(WebGL.LIGHT_SETTINGS.directionalLight.position);

    // ambient light
    this.ambientLight = new THREE.AmbientLight(
      WebGL.LIGHT_SETTINGS.ambientLight.color,
      WebGL.LIGHT_SETTINGS.ambientLight.intensity
    );

    // spot light
    this.spotLight = new THREE.SpotLight(
      WebGL.LIGHT_SETTINGS.spotLight.color,
      WebGL.LIGHT_SETTINGS.spotLight.intensity
    );
    this.spotLight.position.set(
      WebGL.LIGHT_SETTINGS.spotLight.position.x,
      WebGL.LIGHT_SETTINGS.spotLight.position.y,
      WebGL.LIGHT_SETTINGS.spotLight.position.z
    );
    this.spotLight.castShadow = true;
    this.spotLight.shadow.mapSize.set(1024, 1024);
    this.spotLight.shadow.camera.near = 1.0;
    this.spotLight.shadow.camera.far = 5.0;
    this.spotLight.shadow.focus = 1.0;

    // material
    this.material = new THREE.MeshPhongMaterial(WebGL.MATERIAL_SETTINGS);

    // geometry
    this.geometry = new THREE.BoxGeometry(0.5, 0.5, 0.5);

    // mesh & group
    this.mesh = new THREE.Mesh(this.geometry, this.material);
    this.group = new THREE.Group();
    this.group.position.set(0, 0.5, 0);
    this.group.add(this.mesh); 

    // axes helper
    this.axesHelper = new THREE.AxesHelper(WebGL.AXES_SETTINGS.size);

    // spot light helper
    this.spotLightHelper = new THREE.SpotLightHelper(this.spotLight);

    // texture loader
    this.textureLoader = new THREE.TextureLoader();

    // 3DModel loader (GLTF loader & DRACO loader)
    this.gltfLoader = new GLTFLoader();
    this.dracoLoader = new DRACOLoader();
    this.dracoLoader.setDecoderPath('../../node_modules/three/examples/jsm/libs/draco/'); // path to draco decoder
    this.gltfLoader.setDRACOLoader(this.dracoLoader);
    this.model = new THREE.Group();

    // fog
    this.fog = new THREE.Fog(
      WebGL.FOG_SETTINGS.color,
      WebGL.FOG_SETTINGS.near,
      WebGL.FOG_SETTINGS.far
    );

    // raycaster
    this.raycaster = new THREE.Raycaster();

    // scene
    this.scene = new THREE.Scene();
    this.scene.add(this.directionalLight);
    this.scene.add(this.ambientLight);
    this.scene.add(this.spotLight);
    this.scene.add(this.group);
    this.scene.add(this.spotLightHelper);
    this.scene.add(this.axesHelper);
    this.scene.add(this.model);
    this.scene.fog = this.fog;

    // effect composer
    this.effectComposer = new EffectComposer(this.renderer);
    this.renderPass = new RenderPass(this.scene, this.perspectiveCamera);
    // this.renderPass = new RenderPass(this.scene, this.orthographicCamera);
    this.effectComposer.addPass(this.renderPass);

    // post processing pass
    this.glitchPass = new GlitchPass();
    this.effectComposer.addPass(this.glitchPass);
    this.dotScreenPass = new DotScreenPass();
    this.effectComposer.addPass(this.dotScreenPass);

    // controls 
    this.controls = new OrbitControls(this.perspectiveCamera, this.renderer.domElement);
    // this.controls = new OrbitControls(this.orthographicCamera, this.renderer.domElement);

    // clock
    this.clock = new THREE.Clock();

    // binding
    this.render = this.render.bind(this);

    // event handler
    const resizeHandler = () => {
      const host = this.renderer.domElement.parentElement ?? this.renderer.domElement;
      const width  = Math.max(1, Math.floor(host.clientWidth  || window.innerWidth));
      const height = Math.max(1, Math.floor(host.clientHeight || window.innerHeight));
      const dpr = Math.min(2, window.devicePixelRatio || 1);
      // renderer & composer resize
      this.renderer.setPixelRatio(dpr);
      this.renderer.setSize(width, height, true);
      this.effectComposer.setSize(width, height);
      // perspective camera
      this.perspectiveCamera.aspect = width / height;
      this.perspectiveCamera.updateProjectionMatrix();
      // orthographic camera
      this.orthographicCamera.left   = -(WebGL.CAMERA_SCALE * width / height);
      this.orthographicCamera.right  =  (WebGL.CAMERA_SCALE * width / height);
      this.orthographicCamera.top    =   WebGL.CAMERA_SCALE;
      this.orthographicCamera.bottom =  -WebGL.CAMERA_SCALE;
      this.orthographicCamera.updateProjectionMatrix();
    }
    const clickHandler = (event: MouseEvent) => {
      const renderingCanvas = this.renderer.domElement;
      const canvasClientRect = renderingCanvas.getBoundingClientRect();
      // canvas外は無視
      const isPointerInsideCanvas =
        event.clientX >= canvasClientRect.left && event.clientX <= canvasClientRect.right &&
        event.clientY >= canvasClientRect.top && event.clientY <= canvasClientRect.bottom;
      if (!isPointerInsideCanvas) return;
      // canvas座標をNDCへ正規化（-1 ～ 1）
      const normalizedDeviceCoordinateX = ((event.clientX - canvasClientRect.left) / canvasClientRect.width) * 2 - 1;
      const normalizedDeviceCoordinateY = -((event.clientY - canvasClientRect.top) / canvasClientRect.height) * 2 + 1;
      this.raycaster.setFromCamera(
        new THREE.Vector2(normalizedDeviceCoordinateX, normalizedDeviceCoordinateY),
        this.perspectiveCamera
      );
      // this.raycaster.setFromCamera(new THREE.Vector2(normalizedDeviceCoordinateX, normalizedDeviceCoordinateY), this.orthographicCamera);
      const intersectedResults = this.raycaster.intersectObjects(this.group.children, true);
      this.group.children.forEach((childMesh) => {
        if (childMesh instanceof THREE.Mesh) {
          (childMesh.material as THREE.MeshPhongMaterial).color.set(WebGL.MATERIAL_SETTINGS.color);
        }
      });
      if (intersectedResults.length > 0) {
        const intersectedObject = intersectedResults[0].object;
        if (intersectedObject instanceof THREE.Mesh) {
          const meshMaterial = intersectedObject.material as THREE.MeshPhongMaterial;
          meshMaterial.color.set(0xff0000);
          meshMaterial.needsUpdate = true;
        }
      }
    }
    resizeHandler();
    window.addEventListener('resize', resizeHandler, false);
    this.renderer.domElement.addEventListener('click', clickHandler, false);

    // stats
    this.stats = new Stats();
    this.stats.showPanel(0);
    webGLElement.appendChild(this.stats.dom);

    // gui
    this.gui = new GUI();
    const folderPerspectiveCamera = this.gui.addFolder('Perspective Camera');
    folderPerspectiveCamera.add(this.perspectiveCamera.position, 'x', -20, 20, 0.1).name('position X');
    folderPerspectiveCamera.add(this.perspectiveCamera.position, 'y', -20, 20, 0.1).name('position Y');
    folderPerspectiveCamera.add(this.perspectiveCamera.position, 'z', -20, 20, 0.1).name('position Z');
    folderPerspectiveCamera.add(this.perspectiveCamera, 'fov', 5, 60, 1).name('fov').onFinishChange(() => {
      this.perspectiveCamera.updateProjectionMatrix();
    });
    const folderOrthographicCamera = this.gui.addFolder('Orthographic Camera');
    folderOrthographicCamera.add(this.orthographicCamera.position, 'x', -20, 20, 0.1).name('position X');
    folderOrthographicCamera.add(this.orthographicCamera.position, 'y', -20, 20, 0.1).name('position Y');
    folderOrthographicCamera.add(this.orthographicCamera.position, 'z', -20, 20, 0.1).name('position Z');

  }

  // assets loading
  async load() {

    // texture image loading
    const texture = await new THREE.TextureLoader().loadAsync(textureImage);
    texture.colorSpace = THREE.SRGBColorSpace; // three r150+ 推奨
    this.material.map = texture;
    this.material.needsUpdate = true;

    // 3D Model loading
    // const glbPath = '../models/sample.glb';
    // this.gltfLoader.load(glbPath, (gltf) => {
    //   this.model.add(gltf.scene);
    // });

  }

  // rendering DOM
  render() {
    requestAnimationFrame(this.render);
    this.controls.update();

    // elapsed time
    // const time = this.clock.getElapsedTime();
    // console.log(time);

    // renderer render
    // this.renderer.render(this.scene, this.perspectiveCamera);
    // this.renderer.render(this.scene, this.orthographicCamera);

    // effect composer render
    this.effectComposer.render();

    this.stats.update();
  }

}

export default WebGL;
