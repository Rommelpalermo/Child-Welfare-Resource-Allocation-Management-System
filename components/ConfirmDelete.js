'use client';

export default function ConfirmDelete({ action, message = 'Delete this record? This cannot be undone.' }) {
  return (
    <form action={action} className="d-inline" onSubmit={(e) => { if (!confirm(message)) e.preventDefault(); }}>
      <button className="btn btn-sm btn-outline-danger" title="Delete"><i className="bi bi-trash"></i></button>
    </form>
  );
}
