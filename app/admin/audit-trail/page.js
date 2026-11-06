import { db, must } from '@/lib/supabase';
import { requireRole } from '@/lib/session';
import { fmtDate } from '@/lib/format';
import { PageHeader } from '@/components/ui';
import PrintButton from '@/components/PrintButton';

export const metadata = { title: 'Audit Trail — Bahay Pag-asa' };

const LIMIT = 500;
const isDay = (s) => /^\d{4}-\d{2}-\d{2}$/.test(s || '');

export default async function AuditTrail({ searchParams }) {
  await requireRole('admin');
  const sp = await searchParams;
  const from = isDay(sp.date_from) ? sp.date_from : '';
  const to = isDay(sp.date_to) ? sp.date_to : '';
  const userId = /^\d+$/.test(sp.user || '') ? sp.user : '';
  const module = String(sp.module || '').slice(0, 100);
  const q = String(sp.q || '').trim().slice(0, 100).replace(/[%,()*]/g, ' ');

  let query = db()
    .from('activity_logs')
    .select('id, action, module, ip_address, logged_at, user_id, user:users(full_name, username)')
    .order('logged_at', { ascending: false })
    .limit(LIMIT);
  // Manila is UTC+8; interpret the chosen days in that zone.
  if (from) query = query.gte('logged_at', `${from}T00:00:00+08:00`);
  if (to) query = query.lte('logged_at', `${to}T23:59:59.999+08:00`);
  if (userId) query = query.eq('user_id', userId);
  if (module) query = query.eq('module', module);
  if (q) query = query.ilike('action', `%${q}%`);

  const [logs, users, modules] = await Promise.all([
    must(query),
    must(db().from('users').select('id, full_name, username').order('full_name')),
    must(db().from('activity_logs').select('module').not('module', 'is', null).limit(1000)),
  ]);
  const moduleList = [...new Set(modules.map((m) => m.module))].sort();

  return (
    <div className="container-fluid px-4 py-3">
      <PageHeader
        icon="journal-text"
        title="Audit Trail"
        crumbs={[{ label: 'Dashboard', href: '/admin/dashboard' }, { label: 'Audit Trail' }]}
        right={<PrintButton />}
      />
      <div className="card mb-4 d-print-none"><div className="card-body">
        <form method="get" className="row g-3 align-items-end">
          <div className="col-md-2"><label className="form-label fw-semibold">From</label>
            <input type="date" name="date_from" className="form-control" defaultValue={from} /></div>
          <div className="col-md-2"><label className="form-label fw-semibold">To</label>
            <input type="date" name="date_to" className="form-control" defaultValue={to} /></div>
          <div className="col-md-2"><label className="form-label fw-semibold">User</label>
            <select name="user" className="form-select" defaultValue={userId}>
              <option value="">All users</option>
              {users.map((u) => <option key={u.id} value={u.id}>{u.full_name} ({u.username})</option>)}
            </select></div>
          <div className="col-md-2"><label className="form-label fw-semibold">Module</label>
            <select name="module" className="form-select" defaultValue={module}>
              <option value="">All modules</option>
              {moduleList.map((m) => <option key={m} value={m}>{m}</option>)}
            </select></div>
          <div className="col-md-2"><label className="form-label fw-semibold">Search action</label>
            <input name="q" className="form-control" defaultValue={q} placeholder="e.g. Deleted" /></div>
          <div className="col-md-2 d-flex gap-2">
            <button className="btn btn-success"><i className="bi bi-funnel me-1"></i>Filter</button>
            <a href="/admin/audit-trail" className="btn btn-outline-secondary">Reset</a>
          </div>
        </form>
      </div></div>

      <div className="card">
        <div className="card-header">
          <strong>Activity Log</strong>
          <span className="text-muted small ms-2">
            {logs.length} record(s){logs.length === LIMIT ? ` (showing latest ${LIMIT})` : ''}
          </span>
        </div>
        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0">
            <thead className="table-light">
              <tr><th>Date &amp; Time</th><th>User</th><th>Module</th><th>Action</th><th>IP Address</th></tr>
            </thead>
            <tbody>
              {logs.map((l) => (
                <tr key={l.id}>
                  <td className="text-nowrap">{fmtDate(l.logged_at, true)}</td>
                  <td>{l.user ? <>{l.user.full_name} <span className="text-muted small">({l.user.username})</span></> : <span className="text-muted">System / unknown</span>}</td>
                  <td><span className="badge bg-secondary">{l.module || '—'}</span></td>
                  <td>{l.action}</td>
                  <td className="text-muted small">{l.ip_address || '—'}</td>
                </tr>
              ))}
              {logs.length === 0 && <tr><td colSpan="5" className="text-center text-muted py-4">No activity found.</td></tr>}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
