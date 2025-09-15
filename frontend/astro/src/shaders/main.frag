precision highp float;

uniform sampler2D u_texture;
uniform vec2  u_center;
uniform float u_radius;
uniform float u_feather;

varying vec2 v_uv;

void main() {
  vec4 tex = texture2D(u_texture, v_uv);
  float d = distance(v_uv, u_center);
  float mask = 1.0 - smoothstep(u_radius, u_radius + u_feather, d);
  gl_FragColor = vec4(tex.rgb, tex.a * mask);
}
