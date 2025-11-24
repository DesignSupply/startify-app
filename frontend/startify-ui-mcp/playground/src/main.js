import '@designsupply/startify-ui/dist/startify-ui.min.css';
import '@designsupply/startify-ui/dist/startify-ui.min.js';

import { setGeneratedHtml, clearGenerated, loadSampleHtml } from './ui.js';

const ready = document.getElementById('generated');
if (ready) {
  ready.innerHTML = '<p>Ready.</p>';
}

const showBtn = document.getElementById('action-show-sample');
if (showBtn) {
  showBtn.addEventListener('click', async () => {
    try {
      const html = await loadSampleHtml('/src/samples/button.html');
      setGeneratedHtml(html);
    } catch (e) {
      console.error(e);
      alert('サンプル読み込みに失敗しました。');
    }
  });
}

const clearBtn = document.getElementById('action-clear');
if (clearBtn) {
  clearBtn.addEventListener('click', () => {
    clearGenerated();
  });
}

const showDisabledBtn = document.getElementById('action-show-sample-disabled');
if (showDisabledBtn) {
  showDisabledBtn.addEventListener('click', async () => {
    try {
      const html = await loadSampleHtml('/src/samples/button-disabled.html');
      setGeneratedHtml(html);
    } catch (e) {
      console.error(e);
      alert('無効サンプルの読み込みに失敗しました。');
    }
  });
}
