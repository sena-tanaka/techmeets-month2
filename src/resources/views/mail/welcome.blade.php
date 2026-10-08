<x-mail::message>
# {{ $user->name }} さん、ようこそ！

会員登録が完了しました。

<x-mail::button :url="url('/dashboard')">
ダッシュボードを開く
</x-mail::button>

今後ともよろしくお願いします。<br>
{{ config('app.name') }}
</x-mail::message>
