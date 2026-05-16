import { notFound } from 'next/navigation'
import { createClient } from '@/utils/supabase/server'

export default async function ProductDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const supabase = await createClient()
  const { data: product } = await supabase
    .from('products')
    .select('id,title,description,price,demo_url,file_size,categories(name)')
    .eq('id', id)
    .eq('status', 'active')
    .single()

  if (!product) notFound()

  return (
    <main className="container">
      <article className="card">
        <div className="product-image">Digital Product</div>
        <p className="muted">{(product as any).categories?.name ?? 'Product'}</p>
        <h1>{product.title}</h1>
        <p>{product.description}</p>
        <p className="price">${Number(product.price).toFixed(2)}</p>
        {product.file_size ? <p className="muted">File size: {product.file_size}</p> : null}
        <div style={{ display: 'flex', gap: '1rem', flexWrap: 'wrap' }}>
          {product.demo_url ? <a className="button secondary" href={product.demo_url} target="_blank">View demo</a> : null}
          <a className="button" href="/login">Login to purchase</a>
        </div>
      </article>
    </main>
  )
}