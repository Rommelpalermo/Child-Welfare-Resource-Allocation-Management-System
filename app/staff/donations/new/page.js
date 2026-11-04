import { requireRole } from '@/lib/session';
import { entities } from '@/lib/entities';
import { resolveFields } from '@/lib/data';
import { saveEntity } from '@/app/actions';
import { PageHeader } from '@/components/ui';
import EntityForm from '@/components/EntityForm';

export default async function StaffNewDonation() {
  const user = await requireRole('staff');
  const fields = await resolveFields(entities.donations, { role: 'staff', user, creating: true });
  return (
    <div className="container-fluid px-4 py-3">
      <PageHeader icon="gift" title="Add Donation"
        crumbs={[{ label: 'Dashboard', href: '/staff/dashboard' }, { label: 'Donations', href: '/staff/donations' }, { label: 'Add' }]} />
      <div className="card"><div className="card-body">
        <EntityForm fields={fields} action={saveEntity.bind(null, 'donations', null)} cancelHref="/staff/donations" submitLabel="Save Donation" />
      </div></div>
    </div>
  );
}
