import Link from 'next/link';
import { db, must } from '@/lib/supabase';
import { requireRole } from '@/lib/session';
import { entities } from '@/lib/entities';
import { ArchiveToggle, Cell, Flash, PageHeader } from '@/components/ui';
import FilterTable from '@/components/FilterTable';
import ArchiveButton from '@/components/ArchiveButton';
import { setArchived } from '@/app/actions';

export default async function StaffChildren({ searchParams }) {
  await requireRole('staff');
  const sp = await searchParams;
  const e = entities.children;
  const archived = sp?.view === 'archived';
  const rows = await must(db().from('children').select('*').eq('is_archived', archived).order('case_status').order('last_name'));
  return (
    <div className="container-fluid px-4 py-3">
      <Flash sp={sp} />
      <PageHeader icon="person-heart" title="Child Records" crumbs={[{ label: 'Dashboard', href: '/staff/dashboard' }, { label: 'Child Records' }]}
        right={<Link href="/staff/children/new" className="btn btn-success"><i className="bi bi-plus-lg me-1"></i>Add Child Record</Link>} />
      <ArchiveToggle base="/staff/children" archived={archived} />
      <div className="card"><div className="card-body">
        <FilterTable>
          <thead><tr><th>#</th>{e.columns.map((c) => <th key={c.label}>{c.label}</th>)}<th>Action</th></tr></thead>
          <tbody>
            {rows.map((r, i) => (
              <tr key={r.id}>
                <td>{i + 1}</td>
                {e.columns.map((c) => <td key={c.label}><Cell col={c} row={r} /></td>)}
                <td className="text-nowrap">
                  <Link href={`/staff/children/${r.id}/view`} className="btn btn-sm btn-outline-info me-1"><i className="bi bi-eye"></i></Link>
                  <ArchiveButton restore={archived} action={setArchived.bind(null, 'children', r.id, !archived)} />
                </td>
              </tr>
            ))}
          </tbody>
        </FilterTable>
      </div></div>
    </div>
  );
}
