<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use App\Notifications\ContactFormReplyNotification;
use App\Notifications\ContactFormAdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.contact.index');
    }

    public function form(ContactFormRequest $request)
    {
        // 入力内容をセッションに保存
        $request->session()->put('contact_form_inputs', $request->all());

        // 確認画面へリダイレクト
        return redirect()->route('contact.confirm');
    }

    public function confirm(Request $request)
    {
        // セッションから入力内容を取得
        $inputs = $request->session()->get('contact_form_inputs');

        // セッションに入力内容がない場合は入力画面にリダイレクト
        if (!$inputs) {
            return redirect()->route('contact');
        }
        return view('pages.contact.confirm', compact('inputs'));
    }

    public function send(Request $request)
    {

        // POSTデータを取得
        $postData = $request->all();

        // セッションから入力内容を取得
        $inputs = $request->session()->get('contact_form_inputs');

        // POSTデータとセッションデータを組み合わせて使用
        $formData = [];

        // 必須項目がPOSTデータにある場合はそれを使用
        if (!empty($postData) && isset($postData['name']) && isset($postData['email']) && isset($postData['message'])) {
            $formData = [
                'name' => (string)($postData['name'] ?? ''),
                'company' => (string)($postData['company'] ?? ''),
                'email' => (string)($postData['email'] ?? ''),
                'phone' => (string)($postData['phone'] ?? ''),
                'url' => (string)($postData['url'] ?? ''),
                'inquiry_type' => isset($postData['inquiry_type']) && is_array($postData['inquiry_type']) ? $postData['inquiry_type'] : [],
                'gender' => (string)($postData['gender'] ?? ''),
                'message' => (string)($postData['message'] ?? ''),
                'ip_address' => (string)request()->ip(),
                'user_agent' => (string)request()->userAgent(),
            ];
        }
        // セッションデータがある場合はそれを使用
        else if ($inputs) {
            $formData = [
                'name' => (string)($inputs['name'] ?? ''),
                'company' => (string)($inputs['company'] ?? ''),
                'email' => (string)($inputs['email'] ?? ''),
                'phone' => (string)($inputs['phone'] ?? ''),
                'url' => (string)($inputs['url'] ?? ''),
                'inquiry_type' => is_array($inputs['inquiry_type'] ?? null) ? $inputs['inquiry_type'] : [],
                'gender' => (string)($inputs['gender'] ?? ''),
                'message' => (string)($inputs['message'] ?? ''),
                'ip_address' => (string)request()->ip(),
                'user_agent' => (string)request()->userAgent(),
            ];
        }
        // どちらもない場合は入力画面にリダイレクト
        else {
            return redirect()->route('contact');
        }

        try {

            // 自動返信メールの送信
            Notification::route('mail', $formData['email'])
                ->notify(new ContactFormReplyNotification($formData));

            // 管理者へのメール送信
            Notification::route('mail', 'admin@example.com')
                ->notify(new ContactFormAdminNotification($formData));

            // 送信完了フラグをセッションに保存
            $request->session()->put('contact_form_sent', true);

            // セッションから入力内容を削除
            $request->session()->forget('contact_form_inputs');

            return redirect()->route('contact.thanks');
        } catch (\Exception $e) {

            // エラーが発生した場合は入力画面に戻る
            return redirect()->route('contact')
                ->withInput()
                ->withErrors(['email_error' => 'メールの送信に失敗しました。しばらく経ってから再度お試しください。'])
                ->with('status', 'メールの送信に失敗しました。お手数ですが、しばらく経ってから再度お試しください。');
        }
    }

    public function thanks(Request $request)
    {

        // 送信完了フラグがセッションにない場合は入力画面にリダイレクト
        if (!$request->session()->has('contact_form_sent')) {
            return redirect()->route('contact');
        }

        // 送信完了フラグを削除
        $request->session()->forget('contact_form_sent');
        return view('pages.contact.thanks');
    }
}
