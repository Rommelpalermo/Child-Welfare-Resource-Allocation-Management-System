'use server';

import { redirect } from 'next/navigation';
import bcrypt from 'bcryptjs';
import { db } from '@/lib/supabase';
import { createSession, destroySession, getSession, logActivity, requireRole } from '@/lib/session';
import { getEntity } from '@/lib/entities';

const flash = (path, msg, type = 'success') => `${path}?type=${type}&msg=${encodeURIComponent(msg)}`;

export async function login(prev, formData) {
  const username = String(formData.get('username') || '').trim();
  const password = String(formData.get('password') || '');
  if (!username || !password) return { error: 'Please enter both username and password.', username };

  const { data: user } = await db()
    .from('users')
    .select('id, full_name, username, password, role, status')
    .eq('username', username)
    .maybeSingle();

  const ok = user && user.status === 'active' && (await bcrypt.compare(password, user.password));
  if (!ok) {
    await logActivity(null, `Failed login attempt for username "${username.slice(0, 50)}"`, 'Auth');
    return { error: 'Invalid credentials or account is inactive.', username };
  }

  await createSession(user);
  await logActivity(user, 'User logged in', 'Auth');
  redirect(user.role === 'admin' ? '/admin/dashboard' : '/staff/dashboard');
}

export async function logout() {
  const user = await getSession();
  if (user) await logActivity(user, 'User logged out', 'Auth');
  await destroySession();
  redirect('/');
}

function dbError(error) {
  if (/duplicate key/i.test(error.message)) return 'A record with that unique value already exists.';
  return error.message;
}

export async function saveEntity(slug, id, prev, formData) {
  const user = await getSession();
  if (!user) redirect('/');
  const entity = getEntity(slug);
  if (!entity) redirect('/');
  const isAdmin = user.role === 'admin';
  // Staff may only record new donations and new child records.
  if (!isAdmin && !((slug === 'donations' || slug === 'children') && !id)) redirect('/?error=unauthorized');

  const creating = !id;
  const base = `/${user.role}/${slug}`;
  const errors = [];
  const data = {};
  const values = {};

  for (const f of entity.fields) {
    if (f.createOnly && !creating) continue;
    if (f.name === 'password' || f.name === 'confirm') continue;
    if (f.adminOnly && !isAdmin) continue;
    const raw = String(formData.get(f.name) ?? '').trim();
    values[f.name] = raw;
    if (raw === '') {
      if (f.required) errors.push(`${f.label} is required.`);
      data[f.name] = f.type === 'number' ? (f.default ?? null) : null;
    } else if (f.type === 'number' || f.type === 'select' && f.name.endsWith('_id') || f.name === 'received_by' || f.name === 'distributed_by') {
      const n = Number(raw);
      if (Number.isNaN(n)) errors.push(`${f.label} must be a number.`);
      data[f.name] = n;
    } else {
      data[f.name] = raw;
    }
  }
  if (!isAdmin && slug === 'donations') data.received_by = user.id;
  if (slug === 'donation-monitoring' && data.distributed_by == null) data.distributed_by = user.id;

  if (slug === 'accounts') {
    const password = String(formData.get('password') || '');
    const confirm = String(formData.get('confirm') || '');
    if (creating && !password) errors.push('Password is required.');
    if (password && password.length < 6) errors.push('Password must be at least 6 characters.');
    if (password && password !== confirm) errors.push('Passwords do not match.');
    if (!['admin', 'staff'].includes(data.role)) data.role = 'staff';
    if (creating && data.username) {
      const { data: dup } = await db().from('users').select('id').eq('username', data.username).maybeSingle();
      if (dup) errors.push('Username already exists.');
    }
    if (!errors.length && password) data.password = await bcrypt.hash(password, 10);
  }

  if (errors.length) return { errors, values };

  if (slug === 'children' && creating) data.created_by = user.id;

  const q = creating
    ? db().from(entity.table).insert(data).select('id').single()
    : db().from(entity.table).update(data).eq('id', id).select('id').single();
  const { data: saved, error } = await q;
  if (error) return { errors: [dbError(error)], values };

  if (slug === 'children' && creating) {
    await db().from('case_status_history').insert({
      child_id: saved.id,
      new_status: data.case_status,
      remarks: 'Initial record creation.',
      changed_by: user.id,
    });
  }

  const label = data.donor_name || data.full_name || data.org_name || data.item_name || data.first_name || `#${saved.id}`;
  await logActivity(user, `${creating ? 'Added' : 'Updated'} ${entity.singular}: ${label}`, entity.title);
  redirect(flash(base, `${entity.singular} ${creating ? 'added' : 'updated'} successfully.`));
}

export async function deleteEntity(slug, id) {
  const user = await requireRole('admin');
  const entity = getEntity(slug);
  if (!entity) redirect('/');
  const base = `/admin/${slug}`;
  if (slug === 'accounts' && Number(id) === Number(user.id)) {
    redirect(flash(base, 'You cannot delete your own account.', 'warning'));
  }
  const { error } = await db().from(entity.table).delete().eq('id', id);
  if (error) redirect(flash(base, dbError(error), 'danger'));
  await logActivity(user, `Deleted ${entity.singular} ID ${id}`, entity.title);
  redirect(flash(base, `${entity.singular} deleted successfully.`));
}

const CASE_STATUSES = ['Active', 'Closed', 'Referred', 'Reunified'];

export async function updateCase(id, prev, formData) {
  const user = await requireRole('staff');
  const newStatus = String(formData.get('case_status') || '');
  const remarks = String(formData.get('remarks') || '').trim() || null;
  if (!CASE_STATUSES.includes(newStatus)) return { errors: ['Please select a status.'] };

  const { data: child } = await db().from('children').select('id, case_status').eq('id', id).maybeSingle();
  if (!child) redirect('/staff/cases');

  const { error } = await db().from('children').update({ case_status: newStatus }).eq('id', id);
  if (error) return { errors: [error.message] };
  await db().from('case_status_history').insert({
    child_id: child.id,
    old_status: child.case_status,
    new_status: newStatus,
    remarks,
    changed_by: user.id,
  });
  await logActivity(user, `Updated case status for child ID ${id} to ${newStatus}`, 'Cases');
  redirect(flash('/staff/cases', 'Case status updated successfully.'));
}
