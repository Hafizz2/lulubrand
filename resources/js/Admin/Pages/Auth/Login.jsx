import React from 'react';
import { useForm } from '@inertiajs/react';

export default function Login({ flash }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/admin/login');
    };

    return (
        <div className="min-h-screen bg-neutral-950 text-neutral-100 flex items-center justify-center p-4">
            <div className="w-full max-w-md bg-neutral-900 border border-neutral-800 rounded-2xl p-6 sm:p-8 shadow-2xl">
                {/* Brand Header */}
                <div className="text-center mb-8">
                    <div className="inline-flex items-center space-x-2">
                        <span className="text-3xl font-black tracking-widest text-rose-500">LULU</span>
                        <span className="text-xs bg-rose-500/10 text-rose-400 px-2 py-0.5 rounded border border-rose-500/20 font-semibold">ADMIN</span>
                    </div>
                    <p className="text-xs text-neutral-400 mt-2 uppercase tracking-wider">Brand Owner & Staff Portal</p>
                </div>

                {/* Flash Success/Error Message */}
                {flash?.error && (
                    <div className="mb-6 p-3 rounded-lg bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs text-center font-medium">
                        {flash.error}
                    </div>
                )}
                {flash?.success && (
                    <div className="mb-6 p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs text-center font-medium">
                        {flash.success}
                    </div>
                )}

                {/* Form */}
                <form onSubmit={handleSubmit} className="space-y-5">
                    <div>
                        <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-300 mb-1.5">
                            Email Address
                        </label>
                        <input
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            required
                            placeholder="admin@lulu.com"
                            className="w-full px-4 py-3 bg-neutral-950 border border-neutral-800 rounded-xl text-sm text-white placeholder-neutral-500 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-colors"
                        />
                        {errors.email && (
                            <p className="mt-1 text-xs text-rose-400 font-medium">{errors.email}</p>
                        )}
                    </div>

                    <div>
                        <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-300 mb-1.5">
                            Password
                        </label>
                        <input
                            type="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            required
                            placeholder="••••••••"
                            className="w-full px-4 py-3 bg-neutral-950 border border-neutral-800 rounded-xl text-sm text-white placeholder-neutral-500 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-colors"
                        />
                        {errors.password && (
                            <p className="mt-1 text-xs text-rose-400 font-medium">{errors.password}</p>
                        )}
                    </div>

                    <div className="flex items-center justify-between text-xs text-neutral-400">
                        <label className="flex items-center space-x-2 cursor-pointer">
                            <input
                                type="checkbox"
                                checked={data.remember}
                                onChange={(e) => setData('remember', e.target.checked)}
                                className="w-4 h-4 rounded border-neutral-700 bg-neutral-950 text-rose-600 focus:ring-rose-500"
                            />
                            <span>Remember me</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full py-3.5 px-4 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs uppercase tracking-widest rounded-xl transition-all shadow-lg disabled:opacity-50 flex items-center justify-center"
                    >
                        {processing ? 'Authenticating...' : 'Sign In to Console'}
                    </button>
                </form>
            </div>
        </div>
    );
}
