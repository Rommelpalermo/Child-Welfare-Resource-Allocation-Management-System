import { requireRole } from '@/lib/session';
import { entities } from '@/lib/entities';
import { resolveFields } from '@/lib/data';
import { saveEntity } from '@/app/actions';
import { PageHeader } from '@/components/ui';
import EntityForm from '@/components/EntityForm';

export default async function StaffNewChild() {
  const user = await requireRole('staff');
  const fields = await resolveFields(entities.children, { role: 'staff', user, creating: true });
  return (
    <div className="container-fluid px-4 py-3">
      <PageHeader icon="person-heart" title="Add Child Record"
        crumbs={[{ label: 'Dashboard', href: '/staff/dashboard' }, { label: 'Child Records', href: '/staff/children' }, { label: 'Add' }]} />
      <div className="card"><div className="card-body">
        <EntityForm fields={fields} action={saveEntity.bind(null, 'children', null)} cancelHref="/staff/children" submitLabel="Save Child Record" />
      </div></div>
    </div>
  );
}
