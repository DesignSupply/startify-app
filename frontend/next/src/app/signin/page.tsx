import Link from 'next/link';
import SigninForm from '@/components/auth/SigninForm';
import JsonLd from '@/components/JsonLd';

export default function SigninPage() {
  const jsonLdData = [
    {
      '@type': 'ListItem',
      position: 1,
      item: { '@id': `${process.env.APPURL}`, name: 'HOME' },
    },
    {
      '@type': 'ListItem',
      position: 2,
      item: { '@id': `${process.env.APPURL}/signin`, name: 'ログイン' },
    },
  ];

  return (
    <main className="app-main">
      <h1>ログイン</h1>
      <SigninForm />
      <Link href={'/'}>トップページへ</Link>
      <JsonLd jsonLd={jsonLdData} />
    </main>
  );
}
