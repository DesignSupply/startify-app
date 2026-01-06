export const metadata = {
  title: `お探しのページが見つかりません | ${process.env.APPNAME}`,
};

export default function NotFoundErrorPage() {
  return (
    <main className="app-main">
      <h1>404 Error Page Not Found</h1>
      <p>お探しのページが見つかりません</p>
    </main>
  );
}
