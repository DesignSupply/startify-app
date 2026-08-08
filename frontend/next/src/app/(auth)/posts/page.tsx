import { Suspense } from 'react';
import PostsPageContent from './_content';

export default function PostsPage() {
  return (
    <main className="app-main">
      <Suspense fallback={<p>読み込み中...</p>}>
        <PostsPageContent />
      </Suspense>
    </main>
  );
}
