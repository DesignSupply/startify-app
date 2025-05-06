// AjaxPOST投稿トリガー要素指定
const triggers = document.querySelectorAll('.ajax-post-submit');
// AjaxPOST投稿処理
if(0 < triggers.length) {
  triggers.forEach((trigger) => {
    trigger.addEventListener('click', (event) => {
      const params = new URLSearchParams([
        [ 'action', 'ajax_post_action' ],
        [ 'nonce', ajax_object.nonce ],
        [ 'post_id', Number(event.target.getAttribute('data-post-id')) ],
      ]);
      fetch(ajax_object.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: params
      }).then(() => {
        return ;
      }).catch((error) => {
        return error.message;
      });
    });
  })
}