# Deploying YBT Digital on Vercel with Next.js + Supabase

This project is now being rebuilt as a **Next.js App Router** application using Supabase.

## Important production notes

- Supabase Auth is used for Next.js login/signup sessions.
- Product/category data is read from Supabase tables.
- The old PHP files are still present as legacy/reference files, but Vercel will deploy the Next.js app.
- For production product files/images, use Supabase Storage or another object storage service.

## Required Vercel environment variables

Set these in **Vercel Dashboard → Project → Settings → Environment Variables**:

```env
NEXT_PUBLIC_SUPABASE_URL=https://hurizdnsjevcshiwfzcj.supabase.co
NEXT_PUBLIC_SUPABASE_PUBLISHABLE_KEY=sb_publishable_yKJNSoOCPWKPrIVMMgRV3w_jHb07VBh
```

If these are missing in Vercel, the deployed app can fail with a `500` error. After adding or changing env variables, redeploy the project.

This project uses `proxy.ts` for Supabase session refresh because newer Next.js versions deprecate the old `middleware.ts` convention.

## Database setup

1. Open your Supabase project.
2. Open **SQL Editor**.
3. Run `database/next_supabase_schema.sql` for the Next.js/Supabase Auth compatible schema.
4. Optionally adapt/import product data from the old PHP schema.

## Deploy steps

### Option 1: Vercel dashboard

1. Push this project to GitHub/GitLab/Bitbucket.
2. Import the repository in Vercel.
3. Add the environment variables above.
4. Deploy.

### Option 2: Vercel CLI

```bash
npm install
npm run build
npm i -D vercel
npx vercel
npx vercel --prod
```

## After deployment

- Test homepage, products, product detail, login, signup, and account pages.
- Configure Supabase Auth email settings if email confirmation is enabled.
- Add protected checkout/admin/order functionality as the next migration step.