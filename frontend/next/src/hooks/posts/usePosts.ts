'use client';

import { useQuery } from '@tanstack/react-query';
import type { UseQueryOptions } from '@tanstack/react-query';
import { Post, PostListResponse } from '@/types/posts';
import { postSchema, postsSchema } from '@/schemas/posts';
import { apiFetch, baseURL } from '@/helpers/api';
import { USE_MOCK_POSTS, MOCK_POSTS_PATH, PRODUCTION_POSTS_PATH } from '@/features/posts/mockConfig';

const POSTS_QUERY_KEY = ['posts'] as const;

async function fetchPosts(): Promise<PostListResponse> {
  const fallbackOrigin = typeof window !== 'undefined' ? window.location.origin : '';
  const serviceBase = USE_MOCK_POSTS ? fallbackOrigin : baseURL || fallbackOrigin;
  const url = `${serviceBase}${USE_MOCK_POSTS ? MOCK_POSTS_PATH : PRODUCTION_POSTS_PATH}`;
  const data = await apiFetch<PostListResponse>(url);
  const parsed = postsSchema.safeParse(data);
  if (!parsed.success) {
    throw parsed.error;
  }
  return parsed.data;
}

export function usePostsQuery() {
  return useQuery<PostListResponse>({
    queryKey: POSTS_QUERY_KEY,
    queryFn: fetchPosts,
    staleTime: 10_000,
  });
}

type PostQueryOptions = Omit<UseQueryOptions<Post>, 'queryKey' | 'queryFn'>;

export function usePostQuery(id?: Post['id'], options?: PostQueryOptions) {
  return useQuery<Post>({
    queryKey: [...POSTS_QUERY_KEY, id],
    enabled: Boolean(id),
    queryFn: async () => {
      const posts = await fetchPosts();
      const target = posts.find((post) => post.id === id);
      if (!target) {
        throw new Error('Post not found');
      }
      const parsed = postSchema.safeParse(target);
      if (!parsed.success) {
        throw parsed.error;
      }
      return parsed.data;
    },
    staleTime: 0,
    ...options,
  });
}
