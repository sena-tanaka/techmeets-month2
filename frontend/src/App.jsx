import { useCallback, useEffect, useState } from 'react';
import client from './api/client';
import PostForm from './components/PostForm';
import PostList from './components/PostList';

// App:画面全体のまとめ役
// 記事一覧のデータ(posts)を持ち、フォームと一覧の両方に必要なものを配る
function App() {
  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // 記事一覧をAPIから取ってくる関数
  // 「最初の表示時」と「投稿した後」の2か所で使うので、関数として切り出す
  // useCallback:画面が再描画されても、同じ関数を使い回すための仕組み
  const fetchPosts = useCallback(() => {
    return client
      .get('/posts')
      .then((response) => {
        setPosts(response.data.data);
        setError(null);
      })
      .catch((err) => {
        console.error(err);
        setError('記事の取得に失敗しました');
      })
      .finally(() => {
        setLoading(false);
      });
  }, []);

  // 画面が最初に表示されたときに一覧を取得する
  useEffect(() => {
    fetchPosts();
  }, [fetchPosts]);

  return (
    <div>
      <h1>ブログ</h1>

      {/* 投稿が成功したら fetchPosts を呼んでもらう → 一覧が最新になる */}
      <PostForm onCreated={fetchPosts} />

      <h2>記事一覧</h2>
      {/* 読み込み中・エラーでも、フォームは表示したままにする */}
      {loading && <p>読み込み中...</p>}
      {error && <p>{error}</p>}
      {!loading && !error && <PostList posts={posts} />}
    </div>
  );
}

export default App;