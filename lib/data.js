import 'server-only';
import { db, must } from './supabase';

// Resolves select options that come from other tables so the form can be serialized to the client.
export async function resolveFields(entity, { role, user, values = {}, creating }) {
  const out = [];
  for (const f of entity.fields) {
    if (f.createOnly && !creating) continue;
    if (f.adminOnly && role !== 'admin') continue;
    const field = { ...f };
    if (f.ref) {
      const r = f.ref;
      const cols = [r.value, ...(Array.isArray(r.label) ? r.label : [r.label])];
      let q = db().from(r.table).select(cols.join(','));
      if (r.eq) q = q.eq(r.eq[0], r.eq[1]);
      const rows = await must(q.order(r.order, { ascending: !r.desc }));
      field.options = rows.map((row) => ({
        value: String(row[r.value]),
        label: Array.isArray(r.label) ? r.label.map((k) => row[k]).filter(Boolean).join(' · ') : row[r.label],
      }));
      delete field.ref;
    } else if (f.options) {
      field.options = f.options.map((o) => ({ value: o, label: o }));
    }
    let v = values[f.name];
    if (v === undefined || v === null) {
      if (f.selfDefault) v = user.id;
      else if (f.today) v = new Date().toISOString().slice(0, 10);
      else if (f.default !== undefined) v = f.default;
      else v = '';
    }
    field.value = String(v);
    out.push(field);
  }
  return out;
}

export async function getById(table, select, id) {
  if (!/^\d+$/.test(String(id))) return null;
  const { data } = await db().from(table).select(select).eq('id', id).maybeSingle();
  return data;
}
