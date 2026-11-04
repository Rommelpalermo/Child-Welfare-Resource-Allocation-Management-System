import Link from 'next/link';
import { db, must } from '@/lib/supabase';
import { requireRole } from '@/lib/session';
import { entities } from '@/lib/entities';
import { Cell, Flash, PageHeader } from '@/components/ui';
import FilterTable from '@/components/FilterTable';

export default async function StaffDonations({ searchParams }) {
  await requireRole('staff');
  const sp = await searchParams;
  const e = entities.donations;
  const cols = e.columns.filter((c) => c.key !== 'receiver.full_name' && c.key !== 'donation_type');
  const rows = await must(db().from('donations').select(e.select).order('donation_date', { ascending: false }));
  return (
    <div className="container-fluid px-4 py-3">
      <Flash sp={sp} />
      <PageHeader icon="gift" title="Donation Management" crumbs={[{ label: 'Dashboard', href: '/staff/dashboard' }, { label: 'Donations' }]}
        right={<Link href="/staff/donations/new" className="btn btn-success"><i className="bi bi-plus-lg me-1"></i>Add Donation</Link>} />
      <div className="card"><div className="card-body">
        <FilterTable>
          <thead><tr><th>#</th>{cols.map((c) => <th key={c.label}>{c.label}</th>)}</tr></thead>
          <tbody>
            {rows.map((r, i) => (
              <tr key={r.id}><td>{i + 1}</td>{cols.map((c) => <td key={c.label}><Cell col={c} row={r} /></td>)}</tr>
            ))}
          </tbody>
        </FilterTable>
      </div></div>
    </div>
  );
}
