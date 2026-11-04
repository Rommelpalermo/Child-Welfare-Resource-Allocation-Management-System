export function fmtDate(d, withTime = false) {
  if (!d) return '—';
  const date = new Date(d);
  if (isNaN(date)) return '—';
  const opts = { year: 'numeric', month: 'short', day: '2-digit', timeZone: 'Asia/Manila' };
  if (withTime) Object.assign(opts, { hour: '2-digit', minute: '2-digit' });
  return date.toLocaleString('en-US', opts);
}

export function peso(n, digits = 2) {
  return '₱' + Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: digits, maximumFractionDigits: digits });
}

export function fullName(c) {
  return [c.first_name, c.last_name].filter(Boolean).join(' ');
}

export function badgeClass(status) {
  const map = {
    Active: 'success', active: 'success', Received: 'success', Good: 'success', Reunified: 'success', Acknowledged: 'primary',
    Pending: 'warning', Fair: 'warning', Referred: 'info', inactive: 'secondary', Inactive: 'secondary',
    Closed: 'danger', Poor: 'danger', 'For Disposal': 'danger',
  };
  return map[status] || 'secondary';
}

export function flashFrom(sp) {
  if (!sp?.msg) return null;
  return { type: sp.type || 'success', msg: sp.msg };
}
