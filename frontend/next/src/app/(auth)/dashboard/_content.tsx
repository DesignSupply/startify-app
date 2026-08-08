import Link from 'next/link';
import DashboardContents from '@/components/dashboard/DashboardContents';

export default function DashboardPageContent() {
  return (
    <>
      <h1>ダッシュボード</h1>
      <DashboardContents />
      <br />
      <Link href={'/'}>トップページへ</Link>
      <br />
      <Link href={'/posts'}>投稿一覧へ</Link>
    </>
  );
}
