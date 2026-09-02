import HomePageContent from './_content';
import JsonLd from '@/components/JsonLd';

export default function HomePage() {
  const jsonLdData = [
    {
      '@type': 'ListItem',
      position: 1,
      item: { '@id': `${process.env.APPURL}/`, name: 'HOME' },
    },
  ];

  return (
    <main className="app-main">
      <HomePageContent />
      <JsonLd jsonLd={jsonLdData} />
    </main>
  );
}
