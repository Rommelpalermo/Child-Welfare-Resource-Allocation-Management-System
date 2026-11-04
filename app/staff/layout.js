import Shell from '@/components/Shell';
import { requireRole } from '@/lib/session';
import { logout } from '../actions';

export const dynamic = 'force-dynamic';

const links = [
  { href: '/staff/dashboard', icon: 'speedometer2', label: 'Dashboard' },
  { section: 'MY TASKS' },
  { href: '/staff/children', icon: 'person-heart', label: 'View Child Records' },
  { href: '/staff/donations', icon: 'gift', label: 'Donation Management' },
  { href: '/staff/cases', icon: 'clipboard2-pulse', label: 'Update Case Status' },
  { section: 'REPORTS' },
  { href: '/staff/reports', icon: 'file-earmark-bar-graph', label: 'Generate Reports' },
];

export default async function StaffLayout({ children }) {
  const user = await requireRole('staff');
  return <Shell role="staff" fullName={user.full_name} links={links} logoutAction={logout}>{children}</Shell>;
}
