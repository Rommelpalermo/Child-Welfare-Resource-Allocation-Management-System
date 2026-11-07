'use client';

export default function ArchiveButton({ action, restore = false }) {
  const message = restore ? 'Restore this record?' : 'Archive this record? You can restore it later.';
  return (
    <form action={action} className="d-inline me-1" onSubmit={(e) => { if (!confirm(message)) e.preventDefault(); }}>
      <button className={`btn btn-sm ${restore ? 'btn-outline-success' : 'btn-outline-secondary'}`} title={restore ? 'Restore' : 'Archive'}>
        <i className={`bi bi-${restore ? 'arrow-counterclockwise' : 'archive'}`}></i>
      </button>
    </form>
  );
}
