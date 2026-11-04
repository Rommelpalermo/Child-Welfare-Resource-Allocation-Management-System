'use client';

import { useState } from 'react';

// Wraps a server-rendered table and filters its body rows by a search term.
export default function FilterTable({ children, className = 'table table-hover align-middle' }) {
  const [q, setQ] = useState('');
  return (
    <>
      <div className="mb-3" style={{ maxWidth: 320 }}>
        <input className="form-control rounded-pill" placeholder="Search…" value={q} onChange={(e) => setQ(e.target.value.toLowerCase())} />
      </div>
      <div className="table-responsive">
        <table className={className} ref={(el) => {
          if (!el) return;
          el.querySelectorAll('tbody tr').forEach((tr) => {
            tr.style.display = !q || tr.textContent.toLowerCase().includes(q) ? '' : 'none';
          });
        }}>
          {children}
        </table>
      </div>
    </>
  );
}
