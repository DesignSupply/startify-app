'use client';

import { useEffect } from 'react';

export default function NotFoundErrorPage() {
  useEffect(() => {
    document.title = `お探しのページが見つかりません | ${process.env.NEXT_PUBLIC_APPNAME || 'サイト名'}`;
  }, []);

  return (
    <main className="app-main">
      <h1>404 Error Page Not Found</h1>
      <p>お探しのページが見つかりません</p>
    </main>
  );
}
