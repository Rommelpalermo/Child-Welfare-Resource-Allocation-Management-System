# Bahay Pag-asa — Child Welfare & Resource Allocation Management System

Next.js (App Router, JavaScript) app backed by Supabase (PostgreSQL), deployable on Vercel.
The original PHP/MySQL files (`admin/`, `staff/`, `includes/`, `config/`, `database/`) are kept for reference only and are not used by the app.

## Setup

1. Create a project in Supabase, open **SQL Editor**, and run [supabase/schema.sql](supabase/schema.sql).
   For an existing project upgrading to archive support, run [supabase/migration_archive.sql](supabase/migration_archive.sql) in the SQL Editor before deploying the updated app. The migration adds the `is_archived` columns queried by the dashboards and record lists.
2. Copy `.env.example` to `.env.local` and fill in:
   - `SUPABASE_URL` — Project Settings → API → Project URL
   - `SUPABASE_SERVICE_ROLE_KEY` — Project Settings → API → `service_role` key (server-only, never expose it)
   - `SESSION_SECRET` — any long random string
3. `npm install`, then `npm run dev` and open http://localhost:3000.
4. Default logins (both with password `password` — change them after first login): `admin`, `staff`.

## Deploy to Vercel

Add the same three environment variables in Vercel (Project → Settings → Environment Variables), then run `npx vercel --prod` or push to the connected Git repository.
For an existing deployment, run the archive migration in Supabase before deploying the new version. If the deployment already shows a server error, apply the migration now; it is safe to run more than once.
