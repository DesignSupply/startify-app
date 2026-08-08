import SigninPageContent from './_content';
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
      <SigninPageContent />
      <JsonLd jsonLd={jsonLdData} />
    </main>
  );
}
