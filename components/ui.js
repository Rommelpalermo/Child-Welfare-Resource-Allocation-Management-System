import Link from 'next/link';
import { badgeClass, fmtDate, peso, fullName } from '@/lib/format';

export function Flash({ sp }) {
  if (!sp?.msg) return null;
  const cls = ['success', 'warning', 'danger'].includes(sp.type) ? sp.type : 'danger';
  return (
    <div className="flash-container">
      <div className={`alert alert-${cls} shadow`} role="alert">
        <i className={`bi bi-${cls === 'success' ? 'check-circle' : 'exclamation-triangle'}-fill me-2`}></i>
        {sp.msg}
      </div>
    </div>
  );
}

export function PageHeader({ icon, title, crumbs = [], right }) {
  return (
    <div className="page-header">
      <div>
        <h4><i className={`bi bi-${icon} me-2 text-success`}></i>{title}</h4>
        <nav aria-label="breadcrumb">
          <ol className="breadcrumb mb-0">
            {crumbs.map((c, i) => (
              <li key={i} className={`breadcrumb-item${c.href ? '' : ' active'}`}>
                {c.href ? <Link href={c.href}>{c.label}</Link> : c.label}
              </li>
            ))}
          </ol>
        </nav>
      </div>
      {right}
    </div>
  );
}

export function Badge({ value }) {
  if (!value) return '—';
  return <span className={`badge bg-${badgeClass(value)}`}>{value}</span>;
}

export function pick(row, key) {
  return key.split('.').reduce((o, k) => (o == null ? o : o[k]), row);
}

export function Cell({ col, row }) {
  if (col.type === 'name') return fullName(row);
  if (col.type === 'childname') return row.child ? fullName(row.child) : '—';
  const v = pick(row, col.key);
  switch (col.type) {
    case 'badge': return <Badge value={v} />;
    case 'date': return fmtDate(v);
    case 'peso': return peso(v);
    case 'truncate': return v ? (v.length > 40 ? v.slice(0, 40) + '…' : v) : '—';
    default: return v === null || v === undefined || v === '' ? '—' : String(v);
  }
}

export function StatCard({ icon, color, value, label }) {
  return (
    <div className="col-6 col-md-3">
      <div className="card stat-card h-100">
        <div className="card-body d-flex align-items-center gap-3">
          <div className={`icon-box bg-${color} bg-opacity-10 text-${color}`}><i className={`bi bi-${icon}`}></i></div>
          <div>
            <div className={`fs-2 fw-bold text-${color}`}>{value}</div>
            <div className="text-muted small">{label}</div>
          </div>
        </div>
      </div>
    </div>
  );
}
