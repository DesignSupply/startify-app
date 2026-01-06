import fs from 'fs/promises';
import path from 'path';
import { notFound } from 'next/navigation';
import PostDetail from '@/components/posts/PostDetail';
import { Post } from '@/types/posts';
import { postsSchema } from '@/schemas/posts';
import { USE_MOCK_POSTS, MOCK_POSTS_PATH } from '@/features/posts/mockConfig';

const POSTS_JSON_PATH = path.join(
  process.cwd(),
  process.cwd().endsWith(path.join('frontend', 'next')) ? `public${MOCK_POSTS_PATH}` : `frontend/next/public${MOCK_POSTS_PATH}`,
);

// SSG前提のため未生成パラメータは404にする
export const dynamicParams = false;

async function loadPosts(): Promise<Post[]> {
  if (!USE_MOCK_POSTS) {
    throw new Error('USE_MOCK_POSTS=false ではこのページのファイル読み込みは無効です。API経由の取得に切り替えてください。');
  }
  const raw = await fs.readFile(POSTS_JSON_PATH, 'utf-8');
  const parsedPosts = postsSchema.safeParse(JSON.parse(raw));
  if (!parsedPosts.success) {
    throw parsedPosts.error;
  }
  return parsedPosts.data;
}

export async function generateStaticParams() {
  const posts = await loadPosts();
  return posts.map((post) => ({ id: String(post.id) }));
}

export default async function PostDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id: paramId } = await params;
  const id = Number(paramId);
  if (!Number.isFinite(id)) {
    notFound();
  }
  const posts = await loadPosts();
  const post = posts.find((entry) => entry.id === id);
  if (!post) {
    notFound();
  }
  return <PostDetail initialPost={post} />;
}
