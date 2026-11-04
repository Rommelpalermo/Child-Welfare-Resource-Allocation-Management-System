import Link from 'next/link';
import { notFound } from 'next/navigation';
import { db, must } from '@/lib/supabase';
import { getById } from '@/lib/data';
import { fmtDate, fullName } from '@/lib/format';
import { Badge, PageHeader } from '@/components/ui';

export default async function ChildView({ id, role }) {
  const child = await getById('children', '*', id);
  if (!child) notFound();
  const history = await must(
    db().from('case_status_history').select('*, changer:users!changed_by(full_name)').eq('child_id', child.id).order('changed_at', { ascending: false })
  );
  const base = `/${role}/children`;

  const info = [
    ['Case Number', child.case_number], ['Gender', child.gender], ['Birth Date', fmtDate(child.birth_date)],
    ['Admission Date', fmtDate(child.admission_date)], ['Case Type', child.case_type],
    ['Guardian', child.guardian_name], ['Guardian Contact', child.guardian_contact], ['Address', child.address], ['Notes', child.notes],
  ];

  return (
    <div className="container-fluid px-4 py-3">
      <PageHeader
        icon="person-heart"
        title="View Child Record"
        crumbs={[{ label: 'Dashboard', href: `/${role}/dashboard` }, { label: 'Child Records', href: base }, { label: 'View' }]}
        right={role === 'admin' && <Link href={`${base}/${child.id}`} className="btn btn-primary"><i className="bi bi-pencil me-1"></i>Edit</Link>}
      />
      <div className="row g-4">
        <div className="col-lg-7">
          <div className="card">
            <div className="card-header d-flex justify-content-between">
              <strong>{fullName(child)}</strong><Badge value={child.case_status} />
            </div>
            <div className="card-body">
              <dl className="row mb-0">
                {info.map(([k, v]) => (
                  <div key={k} className="row mb-2">
                    <dt className="col-sm-4 text-muted">{k}</dt>
                    <dd className="col-sm-8 mb-0">{v || '—'}</dd>
                  </div>
                ))}
              </dl>
            </div>
          </div>
        </div>
        <div className="col-lg-5">
          <div className="card">
            <div className="card-header"><strong>Status History</strong></div>
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
