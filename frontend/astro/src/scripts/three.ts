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
import fragmentShader from '@/shaders/main.frag?raw';
import vertexShader from '@/shaders/main.vert?raw';
import textureImageUrl from '@/images/webgl_texture_image.jpg?url';

// webGL class
// ライフサイクル設計メモ:
// - constructorは最小限に保つ: ここで非同期・DOM副作用・グローバルイベント登録・ループ開始は行わない。
// - initialize()はThree.jsオブジェクトの生成とシーングラフの組み立て（純同期・非DOM）に限定する。
// - load()はテクスチャ/モデル等の非同期I/O用。constructorでは絶対に行わない。
// - start()/stop()でrequestAnimationFrameレンダーループを制御する。
// - dispose()でリスナー/オブザーバの解除とGPUリソースのdisposeを実施する。
// - DOM操作/イベント登録はattachやbindEvents相当の段階に切り出し、確実に取り外せるようにする。
// - ReactのStrictMode（開発時）はconstructorが二度呼ばれる場合があるため、副作用はconstructorに置かない。
class WebGL {

  static CAMERA_SCALE = 1.0;

  static CAMERA_SETTINGS = {
    perspective: {
      fov: 13, // field of view
      aspect: window.innerWidth / window.innerHeight,
      near: 0.1,
      far: 100,
      position: new THREE.Vector3(6.0, 3.5, 6.0),
      lookAt: new THREE.Vector3(0, 0, 0) // center of the scene
    },
    orthographic: {
      left: ((WebGL.CAMERA_SCALE * window.innerWidth) / window.innerHeight) * -1,
      right: (WebGL.CAMERA_SCALE * window.innerWidth) / window.innerHeight,
      top: WebGL.CAMERA_SCALE,
      bottom: WebGL.CAMERA_SCALE * -1,
      near: 0.1,
      far: 100.0,
      position: new THREE.Vector3(10.0, 10.0, 10.0),
      lookAt: new THREE.Vector3(0, 0, 0) // center of the scene
    }
  };

  static RENDERER_SETTINGS = {
    clearColor: 0x666666,
    alpha: 1.0,
    width: window.innerWidth,
    height: window.innerHeight,
    pixelRatio: window.devicePixelRatio
  };

  static LIGHT_SETTINGS = {
    directionalLight: {
      color: 0xffffff,
      intensity: 1.0,
      position: new THREE.Vector3(-1.0, 1.0, 4.0)
    },
    ambientLight: {
      color: 0xffffff,
      intensity: 0.5
    },
    spotLight: {
      color: 0xffff00,
      intensity: 50.0,
      distance: 100.0,
      angle: Math.PI / 2,
      penumbra: 0.5,
      decay: 2.0,
      position: new THREE.Vector3(3.0, 3.0, 3.0)
    }
  };

  static MATERIAL_SETTINGS = {
    color: 0x0ae0ce,
    wireframe: false,
    transparent: false,
    // opacity: 0.5,
    side: THREE.FrontSide // THREE.DoubleSide, THREE.FrontSide, THREE.BackSide
  };

  static AXES_SETTINGS = {
    size: 10.0
  };

  static FOG_SETTINGS = {
    color: 0xffffff,
    near: 0.1,
    far: 30
  };

  // event handler
  private resizeHandler = (appWidth?: number, appHeight?: number) => {
    const host = this.webGLElement ?? this.renderer!.domElement.parentElement ?? this.renderer!.domElement;
    const width = Math.max(1, Math.floor(appWidth || host.clientWidth || window.innerWidth));
    const height = Math.max(1, Math.floor(appHeight || host.clientHeight || window.innerHeight));
    if (this.lastSize && this.lastSize.width === width && this.lastSize.height === height) return;
    const dpr = Math.min(2, window.devicePixelRatio || 1);
    // renderer & composer resize
    this.renderer!.setPixelRatio(dpr);
    this.renderer!.setSize(width, height, false);
    this.effectComposer!.setSize(width, height);
    // perspective camera
    this.perspectiveCamera!.aspect = width / height;
    this.perspectiveCamera!.updateProjectionMatrix();
    // orthographic camera
    this.orthographicCamera!.left = -((WebGL.CAMERA_SCALE * width) / height);
    this.orthographicCamera!.right = (WebGL.CAMERA_SCALE * width) / height;
    this.orthographicCamera!.top = WebGL.CAMERA_SCALE;
    this.orthographicCamera!.bottom = -WebGL.CAMERA_SCALE;
    this.orthographicCamera!.updateProjectionMatrix();
    this.lastSize = { width, height };
  };

  private clickHandler = (event: MouseEvent) => {
    const renderingCanvas = this.renderer!.domElement;
    const canvasClientRect = renderingCanvas.getBoundingClientRect();
    // canvas外は無視
    const isPointerInsideCanvas =
      event.clientX >= canvasClientRect.left &&
      event.clientX <= canvasClientRect.right &&
      event.clientY >= canvasClientRect.top &&
      event.clientY <= canvasClientRect.bottom;
    if (!isPointerInsideCanvas) return;
    // canvas座標をNDCへ正規化（-1 ～ 1）
    const normalizedDeviceCoordinateX = ((event.clientX - canvasClientRect.left) / canvasClientRect.width) * 2 - 1;
    const normalizedDeviceCoordinateY = -((event.clientY - canvasClientRect.top) / canvasClientRect.height) * 2 + 1;
    this.raycaster!.setFromCamera(
      new THREE.Vector2(normalizedDeviceCoordinateX, normalizedDeviceCoordinateY),
      this.perspectiveCamera!
    );
    // this.raycaster.setFromCamera(new THREE.Vector2(normalizedDeviceCoordinateX, normalizedDeviceCoordinateY), this.orthographicCamera);
    const intersectedResults = this.raycaster!.intersectObjects(this.group!.children, true);
    if (intersectedResults.length > 0) {
      const hit = intersectedResults[0];
      console.log('raycaster hit', {
        name: hit.object.name,
        uuid: hit.object.uuid,
        point: hit.point,
        distance: hit.distance
      });
    } else {
      console.log('raycaster no hit');
    }
  };

  private requestAnimationFrameId?: number | null;

  private onResize = () => this.resizeHandler();
  
  private lastSize?: { width: number; height: number };

  initializeWidth: number;
  initializeHeight: number;
  webGLElement: HTMLElement;
  renderer?: THREE.WebGLRenderer;
  scene?: THREE.Scene;
  perspectiveCamera?: THREE.PerspectiveCamera;
  orthographicCamera?: THREE.OrthographicCamera;
  directionalLight?: THREE.DirectionalLight;
  ambientLight?: THREE.AmbientLight;
  spotLight?: THREE.SpotLight;
  material?: THREE.ShaderMaterial;
  geometry?: THREE.BoxGeometry;
  mesh?: THREE.Mesh;
  group?: THREE.Group;
  spotLightHelper?: THREE.SpotLightHelper;
  axesHelper?: THREE.AxesHelper;
  textureLoader?: THREE.TextureLoader;
  fog?: THREE.Fog;
  effectComposer?: EffectComposer;
  renderPass?: RenderPass;
  glitchPass?: GlitchPass;
  dotScreenPass?: DotScreenPass;
  controls?: OrbitControls;
  gltfLoader?: GLTFLoader;
  dracoLoader?: DRACOLoader;
  model?: THREE.Group;
  raycaster?: THREE.Raycaster;
  clock?: THREE.Clock;
  stats?: Stats;
  gui?: GUI;
  resizeObserver?: ResizeObserver;

  constructor(
    webGLElement: HTMLElement,
    options: {
      width: number;
      height: number;
    }
  ) {

    // app settings override
    this.initializeWidth = options.width;
    this.initializeHeight = options.height;
    this.webGLElement = webGLElement;

  }

  // 1. initialize
  initialize() {
    
    // instance settings (do not mutate static defaults)
    const initWidth = this.initializeWidth;
    const initHeight = this.initializeHeight;
    const initAspect = initWidth / initHeight;

    // renderer
    this.renderer = new THREE.WebGLRenderer();
    this.renderer.setSize(initWidth, initHeight);
    this.renderer.setPixelRatio(WebGL.RENDERER_SETTINGS.pixelRatio);
    this.renderer.setClearColor(WebGL.RENDERER_SETTINGS.clearColor);
    this.renderer.setClearAlpha(WebGL.RENDERER_SETTINGS.alpha);
    this.renderer.shadowMap.enabled = true;

    // perspective camera
    this.perspectiveCamera = new THREE.PerspectiveCamera(
      WebGL.CAMERA_SETTINGS.perspective.fov,
      initAspect,
      WebGL.CAMERA_SETTINGS.perspective.near,
      WebGL.CAMERA_SETTINGS.perspective.far
    );
    this.perspectiveCamera.position.copy(WebGL.CAMERA_SETTINGS.perspective.position);
    this.perspectiveCamera.lookAt(WebGL.CAMERA_SETTINGS.perspective.lookAt);

    // orthographic camera
    this.orthographicCamera = new THREE.OrthographicCamera(
      -((WebGL.CAMERA_SCALE * initWidth) / initHeight),
      (WebGL.CAMERA_SCALE * initWidth) / initHeight,
      WebGL.CAMERA_SCALE,
      -WebGL.CAMERA_SCALE,
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
    this.material = new THREE.ShaderMaterial({
      uniforms: {
        u_texture: { value: null },
        u_center: { value: new THREE.Vector2(0.5, 0.5) },
        u_radius: { value: 0.2 },
        u_feather: { value: 0.4 }
      },
      vertexShader: vertexShader,
      fragmentShader: fragmentShader,
      wireframe: WebGL.MATERIAL_SETTINGS.wireframe,
      transparent: WebGL.MATERIAL_SETTINGS.transparent,
      // opacity: WebGL.MATERIAL_SETTINGS.opacity,
      side: WebGL.MATERIAL_SETTINGS.side
    });

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
    this.fog = new THREE.Fog(WebGL.FOG_SETTINGS.color, WebGL.FOG_SETTINGS.near, WebGL.FOG_SETTINGS.far);

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

    // controls。
    this.controls = new OrbitControls(this.perspectiveCamera, this.renderer.domElement);
    // this.controls = new OrbitControls(this.orthographicCamera, this.renderer.domElement);

    // clock
    this.clock = new THREE.Clock();

    // binding
    this.render = this.render.bind(this);

    // stats
    this.stats = new Stats();
    this.stats.showPanel(0);

    // gui
    this.gui = new GUI();
    
    // initialized resize
    this.resizeHandler(this.initializeWidth, this.initializeHeight);
  }

  // 2. assets loading
  async load() {
    // texture image loading
    const texture = await new THREE.TextureLoader().loadAsync(textureImageUrl);
    texture.colorSpace = THREE.SRGBColorSpace; // three r150+ 推奨
    this.material!.uniforms.u_texture.value = texture;
    this.material!.needsUpdate = true;

    // 3D Model loading
    // const glbPath = '../models/sample.glb';
    // this.gltfLoader.load(glbPath, (gltf) => {
    //   this.model.add(gltf.scene);
    // });
  }

  // 3. rendering DOM  
  render() {

    // controls update
    this.controls!.update();

    // elapsed time
    // const time = this.clock.getElapsedTime();
    // console.log(time);

    // renderer render
    this.renderer!.render(this.scene!, this.perspectiveCamera!);
    // this.renderer.render(this.scene, this.orthographicCamera);

    // effect composer render
    // this.effectComposer.render();

    // stats update
    this.stats!.update();
  }

  // 4. start
  start() {
    
    const loop = () => {
      this.render();
      this.requestAnimationFrameId = requestAnimationFrame(loop);
    };
    loop();

  }

  // 5. stop
  stop() {
    
    cancelAnimationFrame(this.requestAnimationFrameId!);
    this.requestAnimationFrameId = null;
    
  }

  // 6. attach
  attach() {
  
    // bind events
    window.addEventListener('resize', this.onResize, false);
    this.resizeObserver = new ResizeObserver((entries) => {
      const rect = entries[0].contentRect;
      this.resizeHandler(Math.floor(rect.width) || 0, Math.floor(rect.height) || 0);
    });
    // 監視対象はホスト要素に固定（canvasは監視しない）
    this.resizeObserver.observe(this.webGLElement);
    this.renderer!.domElement.addEventListener('click', this.clickHandler, false);

    // renderer
    this.webGLElement.appendChild(this.renderer!.domElement);
    this.renderer!.domElement.style.width = '100%';
    this.renderer!.domElement.style.height = '100%';

    // stats
    this.webGLElement.appendChild(this.stats!.dom);

    // gui
    const folderPerspectiveCamera = this.gui!.addFolder('Perspective Camera');
    folderPerspectiveCamera.add(this.perspectiveCamera!.position, 'x', -20, 20, 0.1).name('position X');
    folderPerspectiveCamera.add(this.perspectiveCamera!.position, 'y', -20, 20, 0.1).name('position Y');
    folderPerspectiveCamera.add(this.perspectiveCamera!.position, 'z', -20, 20, 0.1).name('position Z');
    folderPerspectiveCamera
      .add(this.perspectiveCamera!, 'fov', 5, 60, 1)
      .name('fov')
      .onFinishChange(() => {
        this.perspectiveCamera!.updateProjectionMatrix();
      });
    // const folderOrthographicCamera = this.gui.addFolder('Orthographic Camera');
    // folderOrthographicCamera.add(this.orthographicCamera.position, 'x', -20, 20, 0.1).name('position X');
    // folderOrthographicCamera.add(this.orthographicCamera.position, 'y', -20, 20, 0.1).name('position Y');
    // folderOrthographicCamera.add(this.orthographicCamera.position, 'z', -20, 20, 0.1).name('position Z');

    this.resizeHandler();

  }

  // 7. dispose
  dispose() {

    this.stop();

    // unbind events
    window.removeEventListener('resize', this.onResize, false);
    if (this.renderer) {
      this.renderer.domElement.removeEventListener('click', this.clickHandler, false);
    }
    this.resizeObserver?.disconnect();
    this.resizeObserver = undefined;

    this.gui?.destroy();
    if (this.stats?.dom && this.stats.dom.parentElement) {
      this.stats.dom.parentElement.removeChild(this.stats.dom);
    }

    this.renderer?.dispose();
    this.controls?.dispose();
  
  }

  // 8. resize
  resize(width: number, height: number) {

    this.resizeHandler(width, height);

  }

}

export default WebGL;
