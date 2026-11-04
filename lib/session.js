import 'server-only';
import { cookies, headers } from 'next/headers';
import { redirect } from 'next/navigation';
import { SignJWT, jwtVerify } from 'jose';
import { db } from './supabase';

const COOKIE = 'session';

function secret() {
  const s = process.env.SESSION_SECRET;
  if (!s || s.length < 16) throw new Error('SESSION_SECRET must be set (16+ characters).');
  return new TextEncoder().encode(s);
}

export async function createSession(user) {
  const token = await new SignJWT({
    id: user.id,
    username: user.username,
    full_name: user.full_name,
    role: user.role,
  })
    .setProtectedHeader({ alg: 'HS256' })
    .setExpirationTime('8h')
    .sign(secret());
  (await cookies()).set(COOKIE, token, {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    path: '/',
    maxAge: 60 * 60 * 8,
  });
}

export async function destroySession() {
  (await cookies()).delete(COOKIE);
}

export async function getSession() {
  const token = (await cookies()).get(COOKIE)?.value;
  if (!token) return null;
  try {
    const { payload } = await jwtVerify(token, secret());
    return payload;
  } catch {
    return null;
  }
}

export async function requireRole(role) {
  const user = await getSession();
  if (!user) redirect('/');
  if (user.role !== role) redirect('/?error=unauthorized');
  return user;
}

export async function logActivity(user, action, module) {
  const h = await headers();
  const ip = (h.get('x-forwarded-for') || '').split(',')[0].trim();
  await db().from('activity_logs').insert({ user_id: user?.id ?? null, action, module, ip_address: ip });
}
