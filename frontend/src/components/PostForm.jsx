import { useState } from 'react';
import client from '../api/client';

// PostForm:新規作成フォームの部品
// { onCreated }:投稿が成功したときに呼ぶ関数(親の App から渡される)
function PostForm({ onCreated }) {
  // 入力欄ごとの値を state で管理する(課題の要件「入力値を state で管理」)
  const [title, setTitle] = useState('');
  const [content, setContent] = useState('');
  const [category, setCategory] = useState('');

  const [submitting, setSubmitting] = useState(false); // 送信中かどうか(二重送信防止)
  const [errors, setErrors] = useState({});            // 入力エラーの内容

  // 送信ボタンが押されたときの処理
  const handleSubmit = (e) => {
    // フォーム送信時にブラウザがページを再読み込みしてしまうのを止める
    e.preventDefault();

    setSubmitting(true);
    setErrors({}); // 前回のエラー表示を消す

    // axios で POST リクエストを送る。第2引数が送るデータ(JSONになる)
    client
      .post('/posts', { title, content, category })
      .then(() => {
        // 成功したら入力欄を空に戻す
        setTitle('');
        setContent('');
        setCategory('');
        // 親(App)に「投稿できた」と知らせる → App が一覧を再取得する
        onCreated();
      })
      .catch((err) => {
        // err.response?.status:?. は「responseがなければエラーにせずundefinedにする」書き方
        if (err.response?.status === 422) {
          // 422 = 入力チェックに引っかかった。項目ごとのエラー文が入っている
          setErrors(err.response.data.errors);
        } else {
          // 401(トークンが違う)やCORSエラーなど、それ以外の失敗
          console.error(err);
          setErrors({ general: ['投稿に失敗しました'] });
        }
      })
      .finally(() => {
        setSubmitting(false);
      });
  };

  return (
    <form onSubmit={handleSubmit}>
      <div>
        <label>
          タイトル
          {/* value と onChange をセットで書くと、入力欄と state が連動する */}
          <input type="text" value={title} onChange={(e) => setTitle(e.target.value)} />
        </label>
        {/* errors.title があるときだけ、最初のエラー文を表示する */}
        {errors.title && <p>{errors.title[0]}</p>}
      </div>

      <div>
        <label>
          カテゴリー
          <input type="text" value={category} onChange={(e) => setCategory(e.target.value)} />
        </label>
        {errors.category && <p>{errors.category[0]}</p>}
      </div>

      <div>
        <label>
          本文
          <textarea value={content} onChange={(e) => setContent(e.target.value)} />
        </label>
        {errors.content && <p>{errors.content[0]}</p>}
      </div>

      {errors.general && <p>{errors.general[0]}</p>}

      {/* 送信中はボタンを押せなくして、表示も変える */}
      <button type="submit" disabled={submitting}>
        {submitting ? '送信中...' : '投稿する'}
      </button>
    </form>
  );
}

export default PostForm;
