import type { Metadata } from 'next'
import './globals.css'

export const metadata: Metadata = {
  title: 'YBT Digital',
  description: 'Premium digital products marketplace powered by Next.js and Supabase',
}

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="en">
      <body>
        <header className="site-header">
          <a className="brand" href="/">YBT Digital</a>
          <nav>
            <a href="/products">Products</a>
            <a href="/login">Login</a>
            <a className="button small" href="/signup">Sign up</a>
          </nav>
        </header>
        {children}
        <footer className="site-footer">© {new Date().getFullYear()} YBT Digital. All rights reserved.</footer>
      </body>
    </html>
  )
}