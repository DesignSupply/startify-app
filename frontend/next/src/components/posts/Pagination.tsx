type PaginationProps = {
  page: number;
  totalPages: number;
  pageNumbers: number[];
  startEllipsisVisible: boolean;
  endEllipsisVisible: boolean;
  onPageChange: (nextPage: number) => void;
};

export default function Pagination({
  page,
  totalPages,
  pageNumbers,
  startEllipsisVisible,
  endEllipsisVisible,
  onPageChange,
}: PaginationProps) {
  return (
    <nav aria-label="投稿一覧ページネーション" className="posts-pagination">
      <button
        type="button"
        onClick={() => onPageChange(1)}
        disabled={page <= 1}
        data-testid="pager-first"
      >
        &lt;&lt;
      </button>
      <button
        type="button"
        onClick={() => onPageChange(Math.max(1, page - 1))}
        disabled={page <= 1}
        data-testid="pager-previous"
      >
        &lt;
      </button>
      {pageNumbers.map((num, index) => (
        <span key={num}>
          {index === 1 && startEllipsisVisible && <span aria-hidden="true">…</span>}
          <button
            type="button"
            onClick={() => onPageChange(num)}
            aria-current={page === num ? 'page' : undefined}
            disabled={page === num}
            data-testid={`pager-${num}`}
          >
            {num}
          </button>
          {index === pageNumbers.length - 2 && endEllipsisVisible && <span aria-hidden="true">…</span>}
        </span>
      ))}
      <button
        type="button"
        onClick={() => onPageChange(Math.min(totalPages, page + 1))}
        disabled={page >= totalPages}
        data-testid="pager-next"
      >
        &gt;
      </button>
      <button
        type="button"
        onClick={() => onPageChange(totalPages)}
        disabled={page >= totalPages}
        data-testid="pager-last"
      >
        &gt;&gt;
      </button>
    </nav>
  );
}
