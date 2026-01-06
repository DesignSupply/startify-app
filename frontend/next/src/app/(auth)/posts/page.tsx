'use client';

import Link from 'next/link';
import { useMemo, useState, useEffect, useCallback } from 'react';
import { usePathname, useRouter, useSearchParams } from 'next/navigation';
import { usePostsQuery } from '@/hooks/posts/usePosts';
import Articles from '@/components/posts/Articles';
import Pagination from '@/components/posts/Pagination';

const ITEMS_PER_PAGE = 5;

const buildPageNumbers = (currentPage: number, totalPages: number) => {
  const pages = new Set<number>();
  pages.add(1);
  pages.add(totalPages);
  for (let offset = -2; offset <= 2; offset += 1) {
    const page = currentPage + offset;
    if (page > 1 && page < totalPages) {
      pages.add(page);
    }
  }
  return Array.from(pages).sort((a, b) => a - b);
};

export default function PostsPage() {
  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const { data: posts, isLoading, isError, error } = usePostsQuery();
  const totalPages = useMemo(() => {
    if (!posts?.length) {
      return 1;
    }
    return Math.max(1, Math.ceil(posts.length / ITEMS_PER_PAGE));
  }, [posts]);
  const pageParam = Number(searchParams.get('page') ?? 1);
  const sanitizedPage = Number.isFinite(pageParam)
    ? Math.max(1, Math.min(totalPages, pageParam))
    : 1;
  const [page, setPage] = useState(sanitizedPage);

  useEffect(() => {
    setPage(sanitizedPage);
  }, [sanitizedPage]);

  const visiblePosts = useMemo(() => {
    if (!posts) return [];
    const start = (page - 1) * ITEMS_PER_PAGE;
    return posts.slice(start, start + ITEMS_PER_PAGE);
  }, [page, posts]);

  const handlePageChange = useCallback(
    (targetPage: number) => {
      if (targetPage === page) return;
      const params = new URLSearchParams(searchParams.toString());
      params.set('page', String(targetPage));
      router.replace(`${pathname}?${params.toString()}`);
    },
    [page, pathname, router, searchParams],
  );

  const pageNumbers = useMemo(() => buildPageNumbers(page, totalPages), [page, totalPages]);
  const startEllipsisVisible =
    pageNumbers.length > 2 && pageNumbers[1] > 2;
  const endEllipsisVisible =
    pageNumbers.length > 2 && pageNumbers[pageNumbers.length - 2] < totalPages - 1;

  return (
    <main className="app-main">
      <h1>投稿一覧</h1>
      {isLoading && <p>読み込み中...</p>}
      {isError && (
        <p role="alert" style={{ color: 'red' }}>
          {error instanceof Error ? error.message : 'データの取得に失敗しました'}
        </p>
      )}
      {!isLoading && !isError && (
        <>
          <Articles posts={visiblePosts} />
          <Pagination
            page={page}
            totalPages={totalPages}
            pageNumbers={pageNumbers}
            startEllipsisVisible={startEllipsisVisible}
            endEllipsisVisible={endEllipsisVisible}
            onPageChange={handlePageChange}
          />
        </>
      )}
      <Link href={'/dashboard'}>ダッシュボードへ</Link>
    </main>
  );
}
