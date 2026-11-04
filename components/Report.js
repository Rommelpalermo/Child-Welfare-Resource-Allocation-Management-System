import { db, must } from '@/lib/supabase';
import { fmtDate, fullName, peso } from '@/lib/format';
import { PageHeader } from '@/components/ui';
import PrintButton from '@/components/PrintButton';

const label = (k) => k.replace(/_/g, ' ').replace(/\b\w/g, (m) => m.toUpperCase());
const isDate = /(_date|_at)$/;

const reports = {
  children: {
    title: 'Child Records Report',
    run: async () => (await must(db().from('children').select('*').order('case_status').order('last_name')))
      .map((c) => ({ case_number: c.case_number, full_name: fullName(c), gender: c.gender, case_type: c.case_type, case_status: c.case_status, admission_date: c.admission_date, guardian_name: c.guardian_name })),
  },
  donations: {
    title: 'Donations Report',
    run: async ({ from, to, user, role }) => {
      let q = db().from('donations').select('*, receiver:users!received_by(full_name)').gte('donation_date', from).lte('donation_date', to).order('donation_date', { ascending: false });
      if (role === 'staff') q = q.eq('received_by', user.id);
      return (await must(q)).map((d) => ({ donor_name: d.donor_name, donation_type: d.donation_type, amount: d.amount, donation_date: d.donation_date, status: d.status, ...(role === 'admin' ? { receiver: d.receiver?.full_name } : {}) }));
    },
  },
  resources: {
    title: 'Resource & Inventory Report', adminOnly: true,
    run: async () => (await must(db().from('resources').select('item_name, category, quantity, unit, condition_status, location').order('category').order('item_name'))),
  },
  partners: {
    title: 'Partner Organizations Report', adminOnly: true,
    run: async () => (await must(db().from('partners').select('org_name, contact_person, contact_email, contact_phone, partnership_type, status').order('status').order('org_name'))),
  },
  cases: {
    title: 'Case Status Updates Report', staffOnly: true,
    run: async ({ user }) => (await must(db().from('case_status_history').select('*, child:children(first_name, last_name)').eq('changed_by', user.id).order('changed_at', { ascending: false })))
      .map((h) => ({ child_name: h.child ? fullName(h.child) : null, old_status: h.old_status, new_status: h.new_status, remarks: h.remarks, changed_at: h.changed_at })),
  },
};

export default async function Report({ role, user, sp }) {
  const available = Object.entries(reports).filter(([, r]) => !(r.adminOnly && role !== 'admin') && !(r.staffOnly && role !== 'staff'));
  const type = available.some(([k]) => k === sp.report_type) ? sp.report_type : 'children';
  const now = new Date();
  const from = /^\d{4}-\d{2}-\d{2}$/.test(sp.date_from || '') ? sp.date_from : `${now.toISOString().slice(0, 7)}-01`;
  const to = /^\d{4}-\d{2}-\d{2}$/.test(sp.date_to || '') ? sp.date_to : now.toISOString().slice(0, 10);
  const report = reports[type];
  const data = await report.run({ from, to, user, role });
  const cols = data.length ? Object.keys(data[0]) : [];

  const fmt = (k, v) => {
    if (v === null || v === undefined || v === '') return '—';
    if (k === 'amount') return peso(v);
    if (isDate.test(k)) return fmtDate(v, k.endsWith('_at'));
    return String(v);
  };

  return (
    <div className="container-fluid px-4 py-3">
      <PageHeader icon="file-earmark-bar-graph" title="Generate Reports" crumbs={[{ label: 'Dashboard', href: `/${role}/dashboard` }, { label: 'Reports' }]} />
      <div className="card mb-4 d-print-none"><div className="card-body">
        <form className="row g-3 align-items-end" method="get">
          <div className="col-md-4">
            <label className="form-label fw-semibold">Report Type</label>
            <select name="report_type" className="form-select" defaultValue={type}>
              {available.map(([k, r]) => <option key={k} value={k}>{r.title}</option>)}
            </select>
          </div>
          <div className="col-md-3"><label className="form-label fw-semibold">From</label><input type="date" name="date_from" className="form-control" defaultValue={from} /></div>
          <div className="col-md-3"><label className="form-label fw-semibold">To</label><input type="date" name="date_to" className="form-control" defaultValue={to} /></div>
          <div className="col-md-2 d-flex gap-2">
            <button className="btn btn-success"><i className="bi bi-funnel me-1"></i>Generate</button>
            <PrintButton />
          </div>
        </form>
      </div></div>
      <div className="card">
        <div className="card-header"><strong>{report.title}</strong>
          <span className="text-muted small ms-2">{data.length} record(s)</span></div>
        <div className="table-responsive">
          <table className="table table-striped mb-0">
            <thead className="table-success"><tr>{cols.map((c) => <th key={c}>{label(c)}</th>)}</tr></thead>
            <tbody>
              {data.map((row, i) => <tr key={i}>{cols.map((c) => <td key={c}>{fmt(c, row[c])}</td>)}</tr>)}
              {data.length === 0 && <tr><td className="text-center text-muted py-4">No data for the selected filters.</td></tr>}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
