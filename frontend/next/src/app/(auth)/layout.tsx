import AuthGuard from '@/components/auth/AuthGuard';

export default function AuthRootLayout({ children }: { children: React.ReactNode }) {
  return (
    <AuthGuard>{children}</AuthGuard>
  );
}
