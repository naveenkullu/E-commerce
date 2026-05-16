import { createClient } from '@/utils/supabase/server'

export default async function ProductsPage() {
  const supabase = await createClient()
  const { data: products, error } = await supabase
    .from('products')
    .select('id,title,description,price,views,categories(name)')
    .eq('status', 'active')
    .order('created_at', { ascending: false })

  return (
    <main className="container">
      <div className="section-head">
        <div>
          <h2>Products</h2>
          <p className="muted">Browse products from Supabase.</p>
        </div>
      </div>

      {error ? <div className="alert">Failed to load products: {error.message}</div> : null}

      <div className="grid">
        {(products ?? []).map((product: any) => (
          <article className="card" key={product.id}>
            <div className="product-image">Digital Product</div>
            <p className="muted">{product.categories?.name ?? 'Product'}</p>
            <h3>{product.title}</h3>
            <p>{product.description?.slice(0, 140) ?? 'Premium digital product'}...</p>
            <p className="price">${Number(product.price).toFixed(2)}</p>
            <a className="button" href={`/products/${product.id}`}>View details</a>
          </article>
        ))}
      </div>
    </main>
  )
}