import { redirect } from 'next/navigation'
import { createClient } from '@/utils/supabase/server'

export default async function AccountPage() {
  const supabase = await createClient()
  const { data: { user } } = await supabase.auth.getUser()

  if (!user) redirect('/login')

  return (
    <main className="container">
      <div className="card">
        <h1>My Account</h1>
        <p className="muted">Signed in as {user.email}</p>
        <p>This is the starting point for orders, downloads, and profile settings.</p>
      </div>
    </main>
  )
}