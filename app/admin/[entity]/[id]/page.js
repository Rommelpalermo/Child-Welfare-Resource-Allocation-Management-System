import { notFound } from 'next/navigation';
import { requireRole } from '@/lib/session';
import { getEntity } from '@/lib/entities';
import { getById, resolveFields } from '@/lib/data';
import { saveEntity } from '@/app/actions';
import { PageHeader } from '@/components/ui';
import EntityForm from '@/components/EntityForm';

export default async function EditEntity({ params }) {
  const user = await requireRole('admin');
  const { entity: slug, id } = await params;
  const entity = getEntity(slug);
  if (!entity) notFound();
  const row = await getById(entity.table, entity.select, id);
  if (!row) notFound();
  const fields = await resolveFields(entity, { role: 'admin', user, values: row, creating: false });
  const base = `/admin/${slug}`;

  return (
    <div className="container-fluid px-4 py-3">
      <PageHeader
        icon={entity.icon}
        title={`Edit ${entity.singular}`}
        crumbs={[{ label: 'Dashboard', href: '/admin/dashboard' }, { label: entity.title, href: base }, { label: 'Edit' }]}
      />
      <div className="card"><div className="card-body">
        <EntityForm
          fields={fields}
          action={saveEntity.bind(null, slug, row.id)}
          cancelHref={base}
          submitLabel="Update"
          extra={slug === 'accounts' ? { passwordHint: 'Leave blank to keep the current password.' } : undefined}
        />
      </div></div>
    </div>
  );
}
