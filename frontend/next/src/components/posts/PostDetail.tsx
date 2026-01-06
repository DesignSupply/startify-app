'use client';

import Link from 'next/link';
import Article from '@/components/posts/Article';
import { Post } from '@/types/posts';
import { usePostQuery } from '@/hooks/posts/usePosts';

type PostDetailProps = {
  initialPost: Post;
};

export default function PostDetail({ initialPost }: PostDetailProps) {
  const { data: post, isLoading, isError, error } = usePostQuery(initialPost.id, {
    initialData: initialPost,
  });

  if (isLoading) {
    return <p>読み込み中...</p>;
  }

  if (isError || !post) {
    return (
      <div role="alert">
        <p>{error instanceof Error ? error.message : '投稿を取得できませんでした。'}</p>
        <Link href="/posts">投稿一覧へ</Link>
      </div>
    );
  }

  return (
    <section>
      <Article post={post} showLink={false} />
      <Link href="/posts">投稿一覧へ</Link>
    </section>
  );
}
