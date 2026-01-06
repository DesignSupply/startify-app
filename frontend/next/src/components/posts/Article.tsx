import { Post } from '@/types/posts';
import { formatDate } from '@/utils/formatDate';

type ArticleProps = {
  post: Post;
  excerptLength?: number;
  showLink?: boolean;
};

const createArticleMarkup = (body: string, limit?: number) => {
  if (typeof limit === 'number' && body.length > limit) {
    return { __html: `${body.slice(0, limit)}…` };
  }
  return { __html: body };
};

export default function Article({ post, excerptLength }: ArticleProps) {
  return (
    <section>
      <article data-testid={`post-item-${post.id}`}>
        <h2>{post.title}</h2>
        <p>
          投稿日：
          <time dateTime={post.publishedAt}>{formatDate(post.publishedAt)}</time>
        </p>
        <p>投稿者：{post.author}</p>
        <p
          dangerouslySetInnerHTML={createArticleMarkup(post.body, excerptLength)}
        />
      </article>
    </section>
  );
}
