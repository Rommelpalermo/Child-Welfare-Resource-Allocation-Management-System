import { notFound } from 'next/navigation';
import { db, must } from '@/lib/supabase';
import { requireRole } from '@/lib/session';
import { getById } from '@/lib/data';
import { updateCase } from '@/app/actions';
import { fmtDate, fullName } from '@/lib/format';
import { Badge, PageHeader } from '@/components/ui';
import EntityForm from '@/components/EntityForm';

export default async function UpdateCase({ params }) {
  await requireRole('staff');
  const { id } = await params;
  const child = await getById('children', '*', id);
  if (!child) notFound();
  const history = await must(
    db().from('case_status_history').select('*, changer:users!changed_by(full_name)').eq('child_id', child.id).order('changed_at', { ascending: false }).limit(5)
  );
  const fields = [
    { name: 'case_status', label: 'New Status', type: 'select', required: true, col: 12, value: child.case_status,
      options: ['Active', 'Closed', 'Referred', 'Reunified'].map((o) => ({ value: o, label: o })) },
    { name: 'remarks', label: 'Remarks', type: 'textarea', col: 12, value: '' },
  ];
  return (
    <div className="container-fluid px-4 py-3">
      <PageHeader icon="clipboard2-pulse" title="Update Case Status"
        crumbs={[{ label: 'Dashboard', href: '/staff/dashboard' }, { label: 'Cases', href: '/staff/cases' }, { label: 'Update' }]} />
      <div className="row g-4">
        <div className="col-lg-6">
          <div className="card">
            <div className="card-header d-flex justify-content-between"><strong>{fullName(child)}</strong><Badge value={child.case_status} /></div>
            <div className="card-body">
              <EntityForm fields={fields} action={updateCase.bind(null, child.id)} cancelHref="/staff/cases" submitLabel="Update Status" />
            </div>
          </div>
        </div>
        <div className="col-lg-6">
          <div className="card">
            <div className="card-header"><strong>Recent History</strong></div>
            <div className="card-body">
              {history.length === 0 && <p className="text-muted mb-0">No history.</p>}
              {history.map((h) => (
                <div key={h.id} className="border-bottom pb-2 mb-2">
                  <div>{h.old_status ? <>{h.old_status} → </> : null}<Badge value={h.new_status} /></div>
                  {h.remarks && <div className="small">{h.remarks}</div>}
                  <div className="text-muted small">{fmtDate(h.changed_at, true)} · {h.changer?.full_name || '—'}</div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
