import Link from 'next/link';
import { db, must } from '@/lib/supabase';
import { requireRole } from '@/lib/session';
import { fmtDate, fullName } from '@/lib/format';
import { Badge, Flash, PageHeader } from '@/components/ui';
import FilterTable from '@/components/FilterTable';

export default async function CasesList({ searchParams }) {
  await requireRole('staff');
  const sp = await searchParams;
  const rows = await must(db().from('children').select('*').eq('is_archived', false).order('case_status').order('last_name'));
  return (
    <div className="container-fluid px-4 py-3">
      <Flash sp={sp} />
      <PageHeader icon="clipboard2-pulse" title="Update Case Status" crumbs={[{ label: 'Dashboard', href: '/staff/dashboard' }, { label: 'Cases' }]} />
      <div className="card"><div className="card-body">
        <FilterTable>
          <thead><tr><th>#</th><th>Case No.</th><th>Name</th><th>Case Type</th><th>Current Status</th><th>Last Update</th><th>Action</th></tr></thead>
          <tbody>
            {rows.map((c, i) => (
              <tr key={c.id}>
                <td>{i + 1}</td><td>{c.case_number || '—'}</td><td>{fullName(c)}</td><td>{c.case_type || '—'}</td>
                <td><Badge value={c.case_status} /></td><td>{fmtDate(c.updated_at)}</td>
                <td><Link href={`/staff/cases/${c.id}`} className="btn btn-sm btn-outline-primary"><i className="bi bi-pencil-square me-1"></i>Update</Link></td>
              </tr>
            ))}
          </tbody>
        </FilterTable>
      </div></div>
    </div>
  );
}
