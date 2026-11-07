import Link from 'next/link';
import { db, must } from '@/lib/supabase';
import { requireRole } from '@/lib/session';
import { entities } from '@/lib/entities';
import { Cell, Flash, PageHeader } from '@/components/ui';
import FilterTable from '@/components/FilterTable';

export default async function StaffChildren({ searchParams }) {
  await requireRole('staff');
  const sp = await searchParams;
  const e = entities.children;
  const rows = await must(db().from('children').select('*').order('case_status').order('last_name'));
  return (
    <div className="container-fluid px-4 py-3">
      <Flash sp={sp} />
      <PageHeader icon="person-heart" title="Child Records" crumbs={[{ label: 'Dashboard', href: '/staff/dashboard' }, { label: 'Child Records' }]}
        right={<Link href="/staff/children/new" className="btn btn-success"><i className="bi bi-plus-lg me-1"></i>Add Child Record</Link>} />
      <div className="card"><div className="card-body">
        <FilterTable>
          <thead><tr><th>#</th>{e.columns.map((c) => <th key={c.label}>{c.label}</th>)}<th>Action</th></tr></thead>
          <tbody>
            {rows.map((r, i) => (
              <tr key={r.id}>
                <td>{i + 1}</td>
                {e.columns.map((c) => <td key={c.label}><Cell col={c} row={r} /></td>)}
                <td><Link href={`/staff/children/${r.id}/view`} className="btn btn-sm btn-outline-info"><i className="bi bi-eye"></i></Link></td>
              </tr>
            ))}
          </tbody>
        </FilterTable>
      </div></div>
    </div>
  );
}
