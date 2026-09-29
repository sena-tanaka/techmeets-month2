import axios from 'axios';

// axios.create():URLやヘッダーなどの共通設定を持った「このアプリ専用のaxios」を作る
// → 一覧取得(App)と新規作成(PostForm)の両方で使うので、設定を1か所にまとめる
const client = axios.create({
  // import.meta.env.〇〇:.env.local に書いた値を読み込む
  baseURL: import.meta.env.VITE_API_BASE_URL, // 以降は client.get('/posts') と短く書ける

  headers: {
    // 「JSONで返事してください」とLaravelに伝える(エラーもJSONで返ってくるようになる)
    Accept: 'application/json',
    // トークンを添える決まった書き方。「Bearer(持参人) + 半角スペース + トークン」
    Authorization: `Bearer ${import.meta.env.VITE_API_TOKEN}`,
  },
});

export default client;
