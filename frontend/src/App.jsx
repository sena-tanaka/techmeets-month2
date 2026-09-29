import { useEffect, useState } from 'react';
import axios from 'axios';
import PostList from './components/PostList';

// 呼び出すAPIのURL(nginxが80番ポートなので、ポート番号は省略)
const API_URL = 'http://localhost/api/posts';

function App() {
  // useState:画面に表示する「状態」を入れておく箱
  const [posts, setPosts] = useState([]);       // 記事一覧(最初は空の配列)
  const [loading, setLoading] = useState(true); // 読み込み中かどうか
  const [error, setError] = useState(null);     // エラーメッセージ

  // useEffect:画面が最初に表示された時に1回だけ実行する処理
  // (第2引数の [] が「最初の1回だけ」という意味)
  useEffect(() => {
    axios
      .get(API_URL) // APIにGETリクエストを送る
      .then((response) => {
        // response.data  → axiosが受け取ったJSON全体
        // .data(2つ目) → リソースクラスが包んだ "data" の中身(記事の配列)
        setPosts(response.data.data);
      })
      .catch((err) => {
        // 通信失敗やCORSエラーはここに来る。詳細はブラウザのコンソールで確認
        console.error(err);
        setError('記事の取得に失敗しました');
      })
      .finally(() => {
        // 成功しても失敗しても、読み込み中の表示は終わらせる
        setLoading(false);
      });
  }, []);

  // 状態に応じて表示を切り替える
  if (loading) return <p>読み込み中...</p>;
  if (error) return <p>{error}</p>;

  return (
    <div>
      <h1>ブログ記事一覧</h1>
      {/* 取得した記事をPostListに渡して表示してもらう */}
      <PostList posts={posts} />
    </div>
  );
}

export default App;
