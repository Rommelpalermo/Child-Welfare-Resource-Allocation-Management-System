import { requireRole } from '@/lib/session';
import Report from '@/components/Report';

export default async function StaffReports({ searchParams }) {
  const user = await requireRole('staff');
  return <Report role="staff" user={user} sp={await searchParams} />;
}
