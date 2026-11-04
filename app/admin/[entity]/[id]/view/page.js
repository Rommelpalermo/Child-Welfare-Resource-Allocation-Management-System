import { notFound } from 'next/navigation';
import { requireRole } from '@/lib/session';
import ChildView from '@/components/ChildView';

export default async function AdminView({ params }) {
  await requireRole('admin');
  const { entity, id } = await params;
  if (entity !== 'children') notFound();
  return <ChildView id={id} role="admin" />;
}
