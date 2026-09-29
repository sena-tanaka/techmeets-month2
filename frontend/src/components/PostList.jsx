// PostList:受け取った記事の配列を、一覧として表示するだけの部品
// { posts } は親(App)から渡されるデータ(props)
function PostList({ posts }) {
  // 記事が0件のときのメッセージ
  if (posts.length === 0) {
    return <p>記事がありません</p>;
  }

  return (
    <ul>
      {/* map():配列の各要素を1つずつ<li>に変換する */}
      {/* key:Reactが各行を区別するための目印。重複しないidを使う */}
      {posts.map((post) => (
        <li key={post.id}>
          <h2>{post.title}</h2>
          {/* ?? は「左がnull/undefinedなら右を使う」という意味 */}
          <p>投稿者:{post.author ?? '不明'} / 投稿日:{post.created_at}</p>
          <p>{post.content}</p>
        </li>
      ))}
    </ul>
  );
}

export default PostList;
