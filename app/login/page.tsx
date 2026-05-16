import { login } from './actions'

export default async function LoginPage({ searchParams }: { searchParams: Promise<{ message?: string }> }) {
  const { message } = await searchParams

  return (
    <main className="container">
      <form className="card form" action={login}>
        <h1>Login</h1>
        {message ? <div className="alert">{message}</div> : null}
        <input name="email" type="email" placeholder="Email" required />
        <input name="password" type="password" placeholder="Password" required />
        <button className="button" type="submit">Login</button>
        <p className="muted">No account? <a href="/signup">Create one</a></p>
      </form>
    </main>
  )
}