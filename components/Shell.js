'use client';

import { useState } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';

export default function Shell({ role, fullName, links, logoutAction, children }) {
  const pathname = usePathname();
  const [open, setOpen] = useState(true);

  return (
    <>
      <nav className="navbar navbar-expand-lg topbar px-3 py-2">
        <button className="btn btn-sm btn-outline-light me-3" onClick={() => setOpen(!open)} aria-label="Toggle sidebar">
          <i className="bi bi-list fs-5"></i>
        </button>
        <span className="navbar-brand text-white fw-bold d-flex align-items-center gap-2">
          <i className="bi bi-shield-heart text-warning"></i>
          <span>Bahay Pag-asa</span>
          <span className="badge bg-warning text-dark small">{role.toUpperCase()}</span>
        </span>
        <div className="ms-auto d-flex align-items-center gap-3">
          <span className="text-white-50 small d-none d-md-inline">
            <i className="bi bi-person-circle me-1"></i>{fullName}
          </span>
          <form action={logoutAction}>
            <button className="btn btn-sm btn-outline-light">
              <i className="bi bi-box-arrow-right me-1"></i>Logout
            </button>
          </form>
        </div>
      </nav>

      <div className="wrapper d-flex">
        <nav className={`sidebar${open ? ' show' : ' collapsed'}`} id="sidebar">
          <div className="sidebar-header text-center py-4">
            <i className="bi bi-shield-heart text-warning" style={{ fontSize: '2.5rem' }}></i>
            <div className="text-white fw-bold mt-2">Bahay Pag-asa</div>
            <div className="text-white-50 small">San Antonio, Biñan</div>
          </div>
          <ul className="nav flex-column px-2 pb-4">
            {links.map((l, i) =>
              l.section ? (
                <li key={i} className="nav-section-title">{l.section}</li>
              ) : (
                <li key={i} className="nav-item">
                  <Link href={l.href} className={`nav-link${pathname === l.href || pathname.startsWith(l.href + '/') ? ' active' : ''}`}>
                    <i className={`bi bi-${l.icon} me-2`}></i>{l.label}
                  </Link>
                </li>
              )
            )}
          </ul>
        </nav>
        <div className={`main-content flex-grow-1${open ? '' : ' expanded'}`}>{children}</div>
      </div>
    </>
  );
}
