import { notFound } from 'next/navigation';
import { requireRole } from '@/lib/session';
import { getEntity } from '@/lib/entities';
import { resolveFields } from '@/lib/data';
import { saveEntity } from '@/app/actions';
import { PageHeader } from '@/components/ui';
import EntityForm from '@/components/EntityForm';

export default async function NewEntity({ params }) {
  const user = await requireRole('admin');
  const { entity: slug } = await params;
  const entity = getEntity(slug);
  if (!entity) notFound();
  const fields = await resolveFields(entity, { role: 'admin', user, creating: true });
  const base = `/admin/${slug}`;

  return (
    <div className="container-fluid px-4 py-3">
      <PageHeader
        icon={entity.icon}
        title={`Add ${entity.singular}`}
        crumbs={[{ label: 'Dashboard', href: '/admin/dashboard' }, { label: entity.title, href: base }, { label: 'Add' }]}
      />
      <div className="card"><div className="card-body">
        <EntityForm
          fields={fields}
          action={saveEntity.bind(null, slug, null)}
          cancelHref={base}
          submitLabel={`Save ${entity.singular}`}
        />
      </div></div>
    </div>
  );
}
