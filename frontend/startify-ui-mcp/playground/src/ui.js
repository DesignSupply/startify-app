export function setGeneratedHtml(html) {
  const el = document.getElementById('generated');
  if (!el) return;
  el.innerHTML = html;
}

export function clearGenerated() {
  const el = document.getElementById('generated');
  if (!el) return;
  el.innerHTML = '';
}

export async function loadSampleHtml(path) {
  const res = await fetch(path, { cache: 'no-store' });
  if (!res.ok) throw new Error(`Failed to load sample: ${res.status}`);
  return await res.text();
}

