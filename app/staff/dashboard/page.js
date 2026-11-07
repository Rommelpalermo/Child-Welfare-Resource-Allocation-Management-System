import Link from 'next/link';
import { db, must } from '@/lib/supabase';
import { requireRole } from '@/lib/session';
import { fmtDate, fullName, peso } from '@/lib/format';
import { Badge, PageHeader, StatCard } from '@/components/ui';

export default async function StaffDashboard() {
  const user = await requireRole('staff');

  const [{ count: active }, { count: mine }, recentChildren, recentDonations] = await Promise.all([
    db().from('children').select('*', { count: 'exact', head: true }).eq('is_archived', false).eq('case_status', 'Active'),
    db().from('donations').select('*', { count: 'exact', head: true }).eq('is_archived', false).eq('received_by', user.id),
    must(db().from('children').select('*').eq('is_archived', false).order('created_at', { ascending: false }).limit(6)),
    must(db().from('donations').select('*').eq('is_archived', false).eq('received_by', user.id).order('donation_date', { ascending: false }).limit(5)),
  ]);

  return (
    <div className="container-fluid px-4 py-3">
      <PageHeader icon="speedometer2" title="Staff Dashboard" crumbs={[{ label: 'Home' }]}
        right={<span className="text-muted small"><i className="bi bi-calendar3 me-1"></i>{fmtDate(new Date())}</span>} />
      <div className="row g-3 mb-4">
        <StatCard icon="person-heart" color="success" value={active || 0} label="Active Cases" />
        <StatCard icon="gift" color="primary" value={mine || 0} label="My Donations" />
      </div>
      <div className="row g-4">
        <div className="col-lg-7">
          <div className="card">
            <div className="card-header d-flex justify-content-between"><strong>Recent Child Records</strong>
              <Link href="/staff/children" className="small">View all</Link></div>
            <div className="table-responsive">
              <table className="table table-hover mb-0">
                <thead className="table-light"><tr><th>Name</th><th>Case Type</th><th>Admission</th><th>Status</th></tr></thead>
                <tbody>
                  {recentChildren.map((c) => (
                    <tr key={c.id}><td>{fullName(c)}</td><td>{c.case_type || '—'}</td><td>{fmtDate(c.admission_date)}</td><td><Badge value={c.case_status} /></td></tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div className="col-lg-5">
          <div className="card">
            <div className="card-header d-flex justify-content-between"><strong>My Recent Donations</strong>
              <Link href="/staff/donations" className="small">View all</Link></div>
            <div className="table-responsive">
              <table className="table table-hover mb-0">
                <thead className="table-light"><tr><th>Donor</th><th>Amount</th><th>Date</th></tr></thead>
                <tbody>
                  {recentDonations.map((d) => (
                    <tr key={d.id}><td>{d.donor_name}</td><td>{peso(d.amount)}</td><td>{fmtDate(d.donation_date)}</td></tr>
                  ))}
                  {recentDonations.length === 0 && <tr><td colSpan="3" className="text-center text-muted py-3">None yet.</td></tr>}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
