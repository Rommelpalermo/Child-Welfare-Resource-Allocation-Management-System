'use client';

export default function PrintButton() {
  return <button type="button" className="btn btn-outline-secondary" onClick={() => window.print()}><i className="bi bi-printer me-1"></i>Print</button>;
}
