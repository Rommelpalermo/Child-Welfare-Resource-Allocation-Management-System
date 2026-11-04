'use client';

import { useActionState, useState } from 'react';
import { login } from './actions';

export default function LoginForm({ unauthorized }) {
  const [state, action, pending] = useActionState(login, null);
  const [show, setShow] = useState(false);

  return (
    <form action={action} noValidate>
      {state?.error && (
        <div className="alert alert-danger d-flex align-items-center" role="alert">
          <i className="bi bi-exclamation-triangle-fill me-2"></i>{state.error}
        </div>
      )}
      {unauthorized && <div className="alert alert-warning">You are not authorized to access that page.</div>}
      <div className="mb-3">
        <label className="form-label fw-semibold">Username</label>
        <div className="input-group">
          <span className="input-group-text"><i className="bi bi-person"></i></span>
          <input name="username" className="form-control" placeholder="Enter username" defaultValue={state?.username || ''} required autoFocus />
        </div>
      </div>
      <div className="mb-4">
        <label className="form-label fw-semibold">Password</label>
        <div className="input-group">
          <span className="input-group-text"><i className="bi bi-lock"></i></span>
          <input name="password" type={show ? 'text' : 'password'} className="form-control" placeholder="Enter password" required />
          <button className="btn btn-outline-secondary" type="button" onClick={() => setShow(!show)}>
            <i className={`bi bi-eye${show ? '-slash' : ''}`}></i>
          </button>
        </div>
      </div>
      <button className="btn btn-login btn-success w-100 py-2 text-white" disabled={pending}>
        <i className="bi bi-box-arrow-in-right me-2"></i>{pending ? 'Signing in…' : 'Sign In'}
      </button>
    </form>
  );
}
