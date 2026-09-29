// PostItem:記事「1件」を表示するだけの部品
// { post } は親(PostList)から渡される記事1件分のデータ
function PostItem({ post }) {
  return (
    <li>
      <h3>{post.title}</h3>
      {/* ?? は「左がnull/undefinedなら右を使う」という意味 */}
      <p>投稿者:{post.author ?? '不明'} / 投稿日:{post.created_at}</p>
      <p>{post.content}</p>
    </li>
  );
}

export default PostItem;
