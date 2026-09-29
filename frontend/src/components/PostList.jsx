import PostItem from './PostItem';

// PostList:記事の配列を受け取り、1件ずつ PostItem に表示を任せる部品
// 「一覧をどう並べるか」だけを担当し、「1件をどう見せるか」は PostItem に任せる
function PostList({ posts }) {
  // 記事が0件のときのメッセージ
  if (posts.length === 0) {
    return <p>記事がありません</p>;
  }

  return (
    <ul>
      {/* map():配列の記事1件ずつに対して、PostItem を1つ作る */}
      {/* key は map() で作る一番外側の要素(ここでは PostItem)に付ける */}
      {posts.map((post) => (
        <PostItem key={post.id} post={post} />
      ))}
    </ul>
  );
}

export default PostList;