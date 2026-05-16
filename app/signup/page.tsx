import { signup } from './actions'

export default async function SignupPage({ searchParams }: { searchParams: Promise<{ message?: string }> }) {
  const { message } = await searchParams

  return (
    <main className="container">
      <form className="card form" action={signup}>
        <h1>Create account</h1>
        {message ? <div className="alert">{message}</div> : null}
        <input name="name" type="text" placeholder="Full name" required />
        <input name="email" type="email" placeholder="Email" required />
        <input name="password" type="password" placeholder="Password" required minLength={6} />
        <button className="button" type="submit">Sign up</button>
        <p className="muted">Already have an account? <a href="/login">Login</a></p>
      </form>
    </main>
  )
}