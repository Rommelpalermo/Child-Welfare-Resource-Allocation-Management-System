import { db, must } from '@/lib/supabase';
import { requireRole } from '@/lib/session';
import { fmtDate, fullName, peso } from '@/lib/format';
import { Badge, PageHeader, StatCard } from '@/components/ui';

const count = async (table, filter) => {
  let q = db().from(table).select('*', { count: 'exact', head: true });
  if (filter) q = filter(q);
  const { count: c, error } = await q;
  if (error) throw new Error(error.message);
  return c || 0;
};

export default async function AdminDashboard() {
  await requireRole('admin');
  const today = new Date();
  const monthStart = new Date(Date.UTC(today.getFullYear(), today.getMonth(), 1)).toISOString().slice(0, 10);

  const [children, resources, partners, pending, monthRows, donorRows, recentDonations, recentChildren] = await Promise.all([
    count('children', (q) => q.eq('is_archived', false).eq('case_status', 'Active')),
    count('resources', (q) => q.eq('is_archived', false)),
    count('partners', (q) => q.eq('status', 'Active')),
    count('donations', (q) => q.eq('is_archived', false).eq('status', 'Pending')),
    must(db().from('donations').select('amount').eq('is_archived', false).gte('donation_date', monthStart)),
    must(db().from('donations').select('donor_name').eq('is_archived', false)),
    must(db().from('donations').select('*, receiver:users!received_by(full_name)').eq('is_archived', false).order('created_at', { ascending: false }).limit(5)),
    must(db().from('children').select('*').eq('is_archived', false).order('created_at', { ascending: false }).limit(5)),
  ]);
  const monthTotal = monthRows.reduce((s, r) => s + Number(r.amount || 0), 0);
  const donors = new Set(donorRows.map((r) => r.donor_name)).size;

  return (
    <div className="container-fluid px-4 py-3">
      <PageHeader
        icon="speedometer2"
        title="Admin Dashboard"
        crumbs={[{ label: 'Home' }]}
        right={<span className="text-muted small"><i className="bi bi-calendar3 me-1"></i>{fmtDate(today)}</span>}
      />
      <div className="row g-3 mb-4">
        <StatCard icon="person-heart" color="success" value={children} label="Active Children" />
        <StatCard icon="gift" color="primary" value={peso(monthTotal, 0)} label="Donations This Month" />
        <StatCard icon="box-seam" color="warning" value={resources} label="Resource Items" />
        <StatCard icon="building" color="info" value={partners} label="Active Partners" />
      </div>
      <div className="row g-3 mb-4">
        <StatCard icon="people" color="secondary" value={donors} label="Total Donors" />
        <StatCard icon="hourglass-split" color="danger" value={pending} label="Pending Donations" />
      </div>
      <div className="row g-4">
        <div className="col-lg-7">
          <div className="card">
            <div className="card-header"><strong>Recent Donations</strong></div>
            <div className="table-responsive">
              <table className="table table-hover mb-0">
                <thead className="table-light"><tr><th>Donor</th><th>Type</th><th>Amount</th><th>Date</th><th>Status</th></tr></thead>
                <tbody>
                  {recentDonations.map((d) => (
                    <tr key={d.id}>
                      <td>{d.donor_name}</td><td>{d.donation_type}</td><td>{peso(d.amount)}</td>
                      <td>{fmtDate(d.donation_date)}</td><td><Badge value={d.status} /></td>
                    </tr>
                  ))}
                  {recentDonations.length === 0 && <tr><td colSpan="5" className="text-center text-muted py-3">No donations yet.</td></tr>}
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div className="col-lg-5">
          <div className="card">
            <div className="card-header"><strong>Recently Added Children</strong></div>
            <div className="table-responsive">
              <table className="table table-hover mb-0">
                <thead className="table-light"><tr><th>Name</th><th>Case Type</th><th>Status</th></tr></thead>
                <tbody>
                  {recentChildren.map((c) => (
                    <tr key={c.id}><td>{fullName(c)}</td><td>{c.case_type || '—'}</td><td><Badge value={c.case_status} /></td></tr>
                  ))}
                  {recentChildren.length === 0 && <tr><td colSpan="3" className="text-center text-muted py-3">No records yet.</td></tr>}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
