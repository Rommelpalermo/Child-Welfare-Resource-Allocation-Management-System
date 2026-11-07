import Link from 'next/link';
import { notFound } from 'next/navigation';
import { db, must } from '@/lib/supabase';
import { requireRole } from '@/lib/session';
import { getEntity } from '@/lib/entities';
import { deleteEntity, setArchived } from '@/app/actions';
import { ArchiveToggle, Cell, Flash, PageHeader } from '@/components/ui';
import ArchiveButton from '@/components/ArchiveButton';
import FilterTable from '@/components/FilterTable';
import ConfirmDelete from '@/components/ConfirmDelete';

export default async function EntityList({ params, searchParams }) {
  await requireRole('admin');
  const { entity: slug } = await params;
  const entity = getEntity(slug);
  if (!entity) notFound();
  const sp = await searchParams;

  const archivable = ['children', 'resources', 'donations'].includes(slug);
  const archived = archivable && sp?.view === 'archived';
  let query = db().from(entity.table).select(entity.select).order(entity.order[0], { ascending: entity.order[1] });
  if (archivable) query = query.eq('is_archived', archived);
  const rows = await must(query);
  const base = `/admin/${slug}`;

  return (
    <div className="container-fluid px-4 py-3">
      <Flash sp={sp} />
      <PageHeader
        icon={entity.icon}
        title={entity.title}
        crumbs={[{ label: 'Dashboard', href: '/admin/dashboard' }, { label: entity.title }]}
        right={<Link href={`${base}/new`} className="btn btn-success"><i className="bi bi-plus-lg me-1"></i>Add {entity.singular}</Link>}
      />
      {archivable && <ArchiveToggle base={base} archived={archived} />}
      <div className="card">
        <div className="card-body">
          <FilterTable>
            <thead>
              <tr>
                <th>#</th>
                {entity.columns.map((c) => <th key={c.label}>{c.label}</th>)}
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((row, i) => (
                <tr key={row.id}>
                  <td>{i + 1}</td>
                  {entity.columns.map((c) => <td key={c.label}><Cell col={c} row={row} /></td>)}
                  <td className="text-nowrap">
                    {entity.hasView && (
                      <Link href={`${base}/${row.id}/view`} className="btn btn-sm btn-outline-info me-1" title="View"><i className="bi bi-eye"></i></Link>
                    )}
                    {archivable && archived ? (
                      <ArchiveButton restore action={setArchived.bind(null, slug, row.id, false)} />
                    ) : (
                      <>
                        <Link href={`${base}/${row.id}`} className="btn btn-sm btn-outline-primary me-1" title="Edit"><i className="bi bi-pencil"></i></Link>
                        {archivable && <ArchiveButton action={setArchived.bind(null, slug, row.id, true)} />}
                      </>
                    )}
                    <ConfirmDelete action={deleteEntity.bind(null, slug, row.id)} />
                  </td>
                </tr>
              ))}
              {rows.length === 0 && (
                <tr><td colSpan={entity.columns.length + 2} className="text-center text-muted py-4">No records found.</td></tr>
              )}
            </tbody>
          </FilterTable>
        </div>
      </div>
    </div>
  );
}
