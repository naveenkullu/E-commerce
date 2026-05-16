import { createClient } from '@/utils/supabase/server'

type Product = {
  id: number
  title: string
  slug: string
  description: string | null
  price: number
  views: number | null
  category_name?: string | null
  image?: string | null
}

export default async function HomePage() {
  const supabase = await createClient()

  const { data: products } = await supabase
    .from('products')
    .select('id,title,slug,description,price,views,categories(name),product_screenshots(image_path)')
    .eq('status', 'active')
    .order('views', { ascending: false })
    .limit(8)

  const normalizedProducts = (products ?? []).map((product: any): Product => ({
    id: product.id,
    title: product.title,
    slug: product.slug,
    description: product.description,
    price: Number(product.price),
    views: product.views,
    category_name: product.categories?.name ?? null,
    image: product.product_screenshots?.[0]?.image_path ?? null,
  }))

  return (
    <main>
      <section className="hero">
        <h1>Premium Digital Products</h1>
        <p>Discover ready-to-use themes, templates, apps, eBooks, and tools powered by Next.js and Supabase.</p>
        <a className="button secondary" href="/products">Explore products</a>
      </section>

      <section className="container">
        <div className="section-head">
          <div>
            <h2>Featured Products</h2>
            <p className="muted">Live products from your Supabase database.</p>
          </div>
          <a className="button small" href="/products">View all</a>
        </div>

        <div className="grid">
          {normalizedProducts.length === 0 ? (
            <div className="card">
              <h3>No products yet</h3>
              <p className="muted">Run the Supabase schema and add products to display them here.</p>
            </div>
          ) : (
            normalizedProducts.map((product) => (
              <article className="card" key={product.id}>
                <div className="product-image">{product.image ? product.title : 'Digital Product'}</div>
                <p className="muted">{product.category_name ?? 'Product'}</p>
                <h3>{product.title}</h3>
                <p>{product.description?.slice(0, 110) ?? 'Premium digital product'}...</p>
                <p className="price">${product.price.toFixed(2)}</p>
                <a className="button" href={`/products/${product.id}`}>View details</a>
              </article>
            ))
          )}
        </div>
      </section>
    </main>
  )
}