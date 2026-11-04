import { requireRole } from '@/lib/session';
import Report from '@/components/Report';

export default async function AdminReports({ searchParams }) {
  const user = await requireRole('admin');
  return <Report role="admin" user={user} sp={await searchParams} />;
}
