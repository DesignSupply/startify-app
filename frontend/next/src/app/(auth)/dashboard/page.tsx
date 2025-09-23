import Link from 'next/link';
import DashboardContents from '@/components/dashboard/DashboardContents';

export default function DashboardPage() {
  return (
    <main className="app-main">
      <h1>ダッシュボード</h1>
      <DashboardContents />
      <br />
      <Link href={'/'}>トップページへ</Link>
    </main>
  );
}
