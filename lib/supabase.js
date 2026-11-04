import { createClient } from '@supabase/supabase-js';

let client;

// Server-only client using the service-role key. Never import this from a client component.
export function db() {
  if (!client) {
    const url = process.env.SUPABASE_URL;
    const key = process.env.SUPABASE_SECRET_KEY || process.env.SUPABASE_SERVICE_ROLE_KEY;
    if (!url || !key) throw new Error('SUPABASE_URL and SUPABASE_SECRET_KEY must be set.');
    client = createClient(url, key, { auth: { persistSession: false, autoRefreshToken: false } });
  }
  return client;
}

export async function must(query) {
  const { data, error } = await query;
  if (error) throw new Error(error.message);
  return data;
}
