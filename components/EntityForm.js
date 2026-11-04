'use client';

import { useActionState } from 'react';
import Link from 'next/link';

export default function EntityForm({ fields, action, cancelHref, submitLabel, extra }) {
  const [state, formAction, pending] = useActionState(action, null);

  return (
    <form action={formAction}>
      {state?.errors?.length > 0 && (
        <div className="alert alert-danger">
          <ul className="mb-0">{state.errors.map((e, i) => <li key={i}>{e}</li>)}</ul>
        </div>
      )}
      <div className="row g-3">
        {fields.map((f) => {
          const value = state?.values?.[f.name] ?? f.value;
          const common = { name: f.name, id: f.name, className: 'form-control', defaultValue: value, required: !!f.required };
          return (
            <div key={f.name} className={`col-md-${f.col || 6}`}>
              <label htmlFor={f.name} className="form-label fw-semibold">
                {f.label}{f.required && <span className="text-danger"> *</span>}
              </label>
              {f.type === 'textarea' ? (
                <textarea {...common} rows={3} />
              ) : f.type === 'select' ? (
                <select {...common} className="form-select">
                  {f.blank && <option value="">— Select —</option>}
                  {f.options.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
                </select>
              ) : (
                <input {...common} type={f.type || 'text'} step={f.step} min={f.min} autoComplete={f.type === 'password' ? 'new-password' : undefined} />
              )}
              {f.name === 'password' && extra?.passwordHint && <div className="form-text">{extra.passwordHint}</div>}
            </div>
          );
        })}
      </div>
      <div className="mt-4 d-flex gap-2">
        <button className="btn btn-success" disabled={pending}>
          <i className="bi bi-check-lg me-1"></i>{pending ? 'Saving…' : submitLabel}
        </button>
        <Link href={cancelHref} className="btn btn-outline-secondary">Cancel</Link>
      </div>
    </form>
  );
}
