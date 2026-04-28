// WordPressディレクトリ名
const wordpressDirectory = '';
// テーマディレクトリ名
const themeDirectory = 'startify-classic-theme';
// POST先のPHPファイルパス
const apiUrl = `${location.protocol}//${location.hostname}/${wordpressDirectory}/wp-content/themes/${themeDirectory}/plugins/ajax-loading/loading.php`;
// 初期表示投稿数（投稿取得開始位置）
let current = 3;
// 追加取得投稿数
const add = 3;
// 追加取得トリガー要素指定
const trigger = document.getElementById('infinite_loading_button');
// Ajaxローディング表示のコンテナ要素指定
const container = document.getElementById('infinite_loading_container');
if(trigger) {
  trigger.addEventListener('click', () => {
    const params = new URLSearchParams([
      [ 'currently_loaded_count', current ],
      [ 'additional_loading_count', add ],
    ]);
    fetch(apiUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: params
    })
    .then((response) => {
      return response.json();
    })
    .then((json) => {
      json.content.forEach(function(item) {
        container.insertAdjacentHTML('beforeend', item);
      })
      current = current + add;
      if(json.complete) {
        trigger.remove();
      }
    })
    .catch((error) => {
      return error.message;
    });
  });
}