import Link from 'next/link';
import { Post } from '@/types/posts';
import { formatDate } from '@/utils/formatDate';

type ArticlesProps = {
  posts: Post[];
};

const EXCERPT_PREVIEW_LENGTH = 40;

const createExcerptMarkup = (body: string) => {
  const truncated =
    body.length > EXCERPT_PREVIEW_LENGTH ? `${body.slice(0, EXCERPT_PREVIEW_LENGTH)}…` : body;
  return { __html: truncated };
};

export default function Articles({ posts }: ArticlesProps) {
  if (!posts.length) {
    return <p>表示する投稿はありません。</p>;
  }

  return (
    <section>
      {posts.map((post) => (
        <article key={post.id} data-testid={`post-item-${post.id}`}>
          <h2>{post.title}</h2>
          <p>
            投稿日：
            <time dateTime={post.publishedAt}>{formatDate(post.publishedAt)}</time>
          </p>
          <p>投稿者：{post.author}</p>
          <p
            dangerouslySetInnerHTML={createExcerptMarkup(post.body)}
          />
          <Link href={`/posts/${post.id}`}>詳しく見る</Link>
        </article>
      ))}
    </section>
  );
}
