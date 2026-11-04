import { requireRole } from '@/lib/session';
import ChildView from '@/components/ChildView';

export default async function StaffView({ params }) {
  await requireRole('staff');
  const { id } = await params;
  return <ChildView id={id} role="staff" />;
}
