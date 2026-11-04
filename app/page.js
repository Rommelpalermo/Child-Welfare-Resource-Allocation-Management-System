import { redirect } from 'next/navigation';
import { getSession } from '@/lib/session';
import LoginForm from './LoginForm';

export const metadata = { title: 'Login — Bahay Pag-asa' };

export default async function LoginPage({ searchParams }) {
  const user = await getSession();
  if (user) redirect(user.role === 'admin' ? '/admin/dashboard' : '/staff/dashboard');
  const sp = await searchParams;

  return (
    <div className="d-flex align-items-center justify-content-center py-5" style={{ background: 'linear-gradient(135deg, #1a6b3a 0%, #0d4a2a 100%)', minHeight: '100vh' }}>
      <div className="container" style={{ maxWidth: 440 }}>
        <div className="card" style={{ borderRadius: 16, boxShadow: '0 20px 60px rgba(0,0,0,.35)' }}>
          <div className="text-center text-white p-4" style={{ background: 'linear-gradient(135deg, #28a745, #1a6b3a)', borderRadius: '16px 16px 0 0' }}>
            <div className="mb-3"><i className="bi bi-shield-heart" style={{ fontSize: '3.5rem', opacity: 0.9 }}></i></div>
            <h4 className="fw-bold mb-0">Bahay Pag-asa</h4>
            <small className="opacity-75">Child Welfare &amp; Resource Allocation System</small><br />
            <small className="opacity-75">San Antonio, Biñan City Laguna</small>
          </div>
          <div className="card-body p-4">
            <h5 className="text-center text-muted mb-4">Sign In to Your Account</h5>
            <LoginForm unauthorized={sp?.error === 'unauthorized'} />
          </div>
        </div>
        <p className="text-center text-white opacity-50 mt-3 small">&copy; {new Date().getFullYear()} Bahay Pag-asa. All rights reserved.</p>
      </div>
    </div>
  );
}
