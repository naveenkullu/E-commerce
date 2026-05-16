'use server'

import { redirect } from 'next/navigation'
import { createClient } from '@/utils/supabase/server'

export async function signup(formData: FormData) {
  const supabase = await createClient()
  const name = String(formData.get('name') ?? '')
  const email = String(formData.get('email') ?? '')
  const password = String(formData.get('password') ?? '')

  const { data, error } = await supabase.auth.signUp({
    email,
    password,
    options: { data: { name } },
  })

  if (error) redirect('/signup?message=' + encodeURIComponent(error.message))

  if (data.user) {
    await supabase.from('users').upsert({
      id: data.user.id,
      name,
      email,
      role: 'user',
      status: 'active',
    })
  }

  redirect('/account')
}