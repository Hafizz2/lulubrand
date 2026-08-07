import React, { useState } from 'react';
import { useForm, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function UsersIndex({ users, filters }) {
    const [search, setSearch] = useState(filters.search || '');
    const [roleFilter, setRoleFilter] = useState(filters.role || '');
    const [showAddModal, setShowAddModal] = useState(false);

    const form = useForm({
        name: '',
        email: '',
        phone: '',
        role: 'staff',
        password: '',
    });

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/admin/users', { search, role: roleFilter }, { preserveState: true });
    };

    const handleCreateUser = (e) => {
        e.preventDefault();
        form.post('/admin/users', {
            onSuccess: () => {
                form.reset();
                form.clearErrors();
                setShowAddModal(false);
            },
        });
    };

    const handleRoleChange = (userId, newRole) => {
        router.post(`/admin/users/${userId}/role`, { role: newRole });
    };

    const handleDeleteUser = (userId) => {
        if (!confirm('Are you sure you want to delete this user?')) return;
        router.delete(`/admin/users/${userId}`);
    };

    return (
        <AdminLayout title="User & Staff Management">
            <div className="space-y-6">
                {/* Header Controls */}
                <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white border border-[#E6DFD5] p-4 rounded-xl shadow-sm">
                    <form onSubmit={handleSearch} className="flex gap-2 w-full sm:w-auto flex-1">
                        <input
                            type="text"
                            placeholder="Search name, email, phone..."
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                            className="px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-xs text-stone-900 flex-1 sm:w-64 focus:outline-none focus:border-[#8C6554]"
                        />
                        <select
                            value={roleFilter}
                            onChange={e => {
                                setRoleFilter(e.target.value);
                                router.get('/admin/users', { search, role: e.target.value }, { preserveState: true });
                            }}
                            className="px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                        >
                            <option value="">All Roles</option>
                            <option value="owner">Owners</option>
                            <option value="staff">Staff</option>
                            <option value="cashier">Cashiers</option>
                            <option value="customer">Customers</option>
                        </select>
                        <button type="submit" className="px-4 py-2.5 bg-[#8C6554] text-white text-xs font-bold uppercase rounded-lg shadow-sm">
                            Filter
                        </button>
                    </form>

                    <button
                        onClick={() => {
                            form.clearErrors();
                            setShowAddModal(true);
                        }}
                        className="px-5 py-2.5 bg-[#8C6554] hover:bg-[#755243] text-white font-bold text-xs uppercase tracking-wider rounded-lg shadow-sm transition-all"
                    >
                        + Add Staff Member
                    </button>
                </div>

                {/* Users List Table */}
                <div className="bg-white border border-[#E6DFD5] rounded-xl overflow-hidden shadow-sm">
                    <table className="w-full text-left text-xs text-stone-700">
                        <thead className="bg-[#F9F6F0] text-stone-500 font-bold uppercase tracking-wider border-b border-[#E6DFD5]">
                            <tr>
                                <th className="p-4">User</th>
                                <th className="p-4">Contact</th>
                                <th className="p-4">Role</th>
                                <th className="p-4">Registered</th>
                                <th className="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-[#E6DFD5]">
                            {users.data.map(u => (
                                <tr key={u.id} className="hover:bg-[#F9F6F0]/60 transition-colors">
                                    <td className="p-4">
                                        <span className="font-bold text-[#221F1F] block">{u.name}</span>
                                        <span className="text-[10px] text-stone-500 font-mono">ID #{u.id}</span>
                                    </td>
                                    <td className="p-4">
                                        <span className="text-stone-900 font-medium block">{u.email}</span>
                                        <span className="text-stone-500 text-[11px]">{u.phone || '—'}</span>
                                    </td>
                                    <td className="p-4">
                                        <select
                                            value={u.role}
                                            onChange={e => handleRoleChange(u.id, e.target.value)}
                                            className="px-2.5 py-1 bg-[#F9F6F0] border border-[#E6DFD5] rounded text-xs font-bold uppercase text-[#221F1F]"
                                        >
                                            <option value="owner">Owner</option>
                                            <option value="staff">Staff</option>
                                            <option value="cashier">Cashier</option>
                                            <option value="customer">Customer</option>
                                        </select>
                                    </td>
                                    <td className="p-4 text-stone-500">
                                        {new Date(u.created_at).toLocaleDateString()}
                                    </td>
                                    <td className="p-4 text-right">
                                        <button
                                            onClick={() => handleDeleteUser(u.id)}
                                            className="text-xs text-rose-700 hover:underline font-bold uppercase tracking-wider"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Add Modal */}
                {showAddModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                        <form onSubmit={handleCreateUser} className="bg-white border border-[#E6DFD5] rounded-xl p-6 max-w-md w-full shadow-2xl space-y-4 max-h-[92vh] overflow-y-auto">
                            <div className="flex items-center justify-between border-b border-[#E6DFD5] pb-3">
                                <h3 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F]">Add Staff Member</h3>
                                <button type="button" onClick={() => setShowAddModal(false)} className="text-stone-400 hover:text-stone-900 text-xl">&times;</button>
                            </div>

                            {/* Validation Error Summary Banner */}
                            {Object.keys(form.errors).length > 0 && (
                                <div className="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-xs font-bold space-y-1">
                                    <span className="block font-black uppercase tracking-wider">⚠️ Please fix the following errors:</span>
                                    <ul className="list-disc pl-4 font-medium space-y-0.5">
                                        {Object.entries(form.errors).map(([field, err]) => (
                                            <li key={field}>{err}</li>
                                        ))}
                                    </ul>
                                </div>
                            )}

                            <div>
                                <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Full Name *</label>
                                <input
                                    type="text"
                                    value={form.data.name}
                                    onChange={e => {
                                        form.setData('name', e.target.value);
                                        if (form.errors.name) form.clearErrors('name');
                                    }}
                                    required
                                    className={`w-full px-3.5 py-2.5 bg-[#F9F6F0] border rounded-lg text-sm text-stone-900 focus:outline-none ${form.errors.name ? 'border-rose-500 ring-1 ring-rose-500 bg-rose-50/30' : 'border-[#E6DFD5] focus:border-[#8C6554]'}`}
                                />
                                {form.errors.name && (
                                    <p className="text-[10px] font-bold text-rose-600 uppercase tracking-wider mt-1">{form.errors.name}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Email *</label>
                                <input
                                    type="email"
                                    value={form.data.email}
                                    onChange={e => {
                                        form.setData('email', e.target.value);
                                        if (form.errors.email) form.clearErrors('email');
                                    }}
                                    required
                                    className={`w-full px-3.5 py-2.5 bg-[#F9F6F0] border rounded-lg text-sm text-stone-900 focus:outline-none ${form.errors.email ? 'border-rose-500 ring-1 ring-rose-500 bg-rose-50/30' : 'border-[#E6DFD5] focus:border-[#8C6554]'}`}
                                />
                                {form.errors.email && (
                                    <p className="text-[10px] font-bold text-rose-600 uppercase tracking-wider mt-1">{form.errors.email}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Phone Number</label>
                                <input
                                    type="text"
                                    value={form.data.phone}
                                    onChange={e => {
                                        form.setData('phone', e.target.value);
                                        if (form.errors.phone) form.clearErrors('phone');
                                    }}
                                    className={`w-full px-3.5 py-2.5 bg-[#F9F6F0] border rounded-lg text-sm text-stone-900 focus:outline-none ${form.errors.phone ? 'border-rose-500 ring-1 ring-rose-500 bg-rose-50/30' : 'border-[#E6DFD5] focus:border-[#8C6554]'}`}
                                />
                                {form.errors.phone && (
                                    <p className="text-[10px] font-bold text-rose-600 uppercase tracking-wider mt-1">{form.errors.phone}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Role</label>
                                <select
                                    value={form.data.role}
                                    onChange={e => form.setData('role', e.target.value)}
                                    className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                >
                                    <option value="staff">Staff</option>
                                    <option value="cashier">Cashier</option>
                                    <option value="owner">Owner</option>
                                    <option value="customer">Customer</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Password * (Min 8 Characters)</label>
                                <input
                                    type="password"
                                    value={form.data.password}
                                    onChange={e => {
                                        form.setData('password', e.target.value);
                                        if (form.errors.password) form.clearErrors('password');
                                    }}
                                    required
                                    placeholder="At least 8 characters..."
                                    className={`w-full px-3.5 py-2.5 bg-[#F9F6F0] border rounded-lg text-sm text-stone-900 focus:outline-none ${form.errors.password ? 'border-rose-500 ring-1 ring-rose-500 bg-rose-50/30' : 'border-[#E6DFD5] focus:border-[#8C6554]'}`}
                                />
                                {form.errors.password ? (
                                    <p className="text-[10px] font-bold text-rose-600 uppercase tracking-wider mt-1">{form.errors.password}</p>
                                ) : (
                                    <span className="text-[10px] text-stone-400 block mt-1">Must be at least 8 characters long.</span>
                                )}
                            </div>

                            <div className="flex justify-end space-x-3 pt-2">
                                <button
                                    type="button"
                                    onClick={() => setShowAddModal(false)}
                                    className="px-5 py-2 bg-stone-100 text-stone-700 text-xs font-bold uppercase rounded-lg"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="px-5 py-2 bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase rounded-lg shadow-sm"
                                >
                                    {form.processing ? 'Saving...' : 'Save User'}
                                </button>
                            </div>
                        </form>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
