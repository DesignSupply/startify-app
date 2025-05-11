import '@/styles/globals.css';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import OffCanvas from '@/components/OffCanvas';

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ja">
      <body>
        <div className="app-layout">
          <Header />
          {children}
          <Footer />
        </div>
        <OffCanvas />
      </body>
    </html>
  );
}
