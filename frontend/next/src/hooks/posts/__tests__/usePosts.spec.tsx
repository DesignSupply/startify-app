'use client';

/**
 * usePostsQuery / usePostQuery のテスト:
 * - 正常なモックレスポンスを返して一覧・詳細が受け取れること
 * - Zod スキーマが失敗したときにエラー状態になること
 * - id が無いときはフェッチしないこと
 * - postSchema のバリデーション失敗で error になること
 */

import React from 'react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { renderHook, waitFor } from '@testing-library/react';
import { describe, expect, it, vi, beforeEach, afterEach } from 'vitest';
import { usePostsQuery, usePostQuery } from '../usePosts';
import * as api from '@/helpers/api';
import { ZodError } from 'zod';
import { postsSchema, postSchema } from '@/schemas/posts';

const mockPostsResponse = [
  {
    id: 1,
    publishedAt: '2025-12-01T10:00:00Z',
    author: 'Test 太郎',
    title: 'テスト投稿タイトル1',
    body: '<p>本文です</p>',
  },
] satisfies Array<{
  id: number;
  publishedAt: string;
  author: string;
  title: string;
  body: string;
}>;

const makeQueryClient = () =>
  new QueryClient({
    defaultOptions: {
      queries: {
        retry: false,
      },
    },
  });

const createWrapper = (client: QueryClient) => {
  const Wrapper = ({ children }: { children: React.ReactNode }) => (
    <QueryClientProvider client={client}>{children}</QueryClientProvider>
  );
  Wrapper.displayName = 'PostsQueryClientWrapper';
  return Wrapper;
};

describe('usePosts hooks', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  it('fetches posts and returns data', async () => {
    const client = makeQueryClient();
    const fetchSpy = vi.spyOn(api, 'apiFetch').mockResolvedValue(mockPostsResponse);

    const { result } = renderHook(() => usePostsQuery(), {
      wrapper: createWrapper(client),
    });

    await waitFor(() => expect(result.current.isSuccess).toBe(true));
    expect(result.current.data).toEqual(mockPostsResponse);
    expect(fetchSpy).toHaveBeenCalledTimes(1);
    expect(fetchSpy.mock.calls[0][0]).toContain('/mock/posts.json');
  });

  it('throws error when posts schema fails', async () => {
    const client = makeQueryClient();
    vi.spyOn(api, 'apiFetch').mockResolvedValue(mockPostsResponse);
    const schemaSpy = vi
      .spyOn(postsSchema, 'safeParse')
      .mockReturnValue({
        success: false,
        error: new ZodError([]),
      });

    const { result } = renderHook(() => usePostsQuery(), {
      wrapper: createWrapper(client),
    });

    await waitFor(() => expect(result.current.isError).toBe(true));
    expect(result.current.error).toBeDefined();
    schemaSpy.mockRestore();
  });

  it('returns a post when id is provided', async () => {
    const client = makeQueryClient();
    vi.spyOn(api, 'apiFetch').mockResolvedValue(mockPostsResponse);
    const { result } = renderHook(() => usePostQuery(1), {
      wrapper: createWrapper(client),
    });

    await waitFor(() => expect(result.current.isSuccess).toBe(true));
    expect(result.current.data?.id).toBe(1);
  });

  it('does not fetch when id is undefined', async () => {
    const client = makeQueryClient();
    const fetchSpy = vi.spyOn(api, 'apiFetch').mockResolvedValue(mockPostsResponse);

    const { result } = renderHook(() => usePostQuery(undefined), {
      wrapper: createWrapper(client),
    });

    expect(result.current.status).toBe('pending');
    expect(fetchSpy).not.toHaveBeenCalled();
  });

  it('errors when post validation fails', async () => {
    const client = makeQueryClient();
    vi.spyOn(api, 'apiFetch').mockResolvedValue(mockPostsResponse);
    const parseSpy = vi
      .spyOn(postSchema, 'safeParse')
      .mockReturnValue({
        success: false,
        error: new ZodError([]),
      });

    const { result } = renderHook(() => usePostQuery(1), {
      wrapper: createWrapper(client),
    });

    await waitFor(() => expect(result.current.isError).toBe(true));
    parseSpy.mockRestore();
  });
});
