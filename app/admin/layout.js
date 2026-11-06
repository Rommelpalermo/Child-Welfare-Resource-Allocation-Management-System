import Shell from '@/components/Shell';
import { requireRole } from '@/lib/session';
import { logout } from '../actions';

export const dynamic = 'force-dynamic';

const links = [
  { href: '/admin/dashboard', icon: 'speedometer2', label: 'Dashboard' },
  { section: 'MANAGEMENT' },
  { href: '/admin/accounts', icon: 'people', label: 'Account Management' },
  { href: '/admin/children', icon: 'person-heart', label: 'Child Records' },
  { href: '/admin/resources', icon: 'box-seam', label: 'Resource & Inventory' },
  { href: '/admin/partners', icon: 'building', label: 'Partner Information' },
  { href: '/admin/donations', icon: 'gift', label: 'Donation Management' },
  { href: '/admin/donation-monitoring', icon: 'graph-up', label: 'Donation Monitoring' },
  { section: 'REPORTS' },
  { href: '/admin/reports', icon: 'file-earmark-bar-graph', label: 'Generate Reports' },
  { section: 'SECURITY' },
  { href: '/admin/audit-trail', icon: 'journal-text', label: 'Audit Trail' },
];

export default async function AdminLayout({ children }) {
  const user = await requireRole('admin');
  return <Shell role="admin" fullName={user.full_name} links={links} logoutAction={logout}>{children}</Shell>;
}
