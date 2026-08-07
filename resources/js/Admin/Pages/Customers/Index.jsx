import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Index({ customers }) {
    const [search, setSearch] = useState('');

    const filteredCustomers = customers.data.filter(c => {
        if (!search) return true;
        const q = search.toLowerCase();
        return (
            (c.name && c.name.toLowerCase().includes(q)) ||
            (c.email && c.email.toLowerCase().includes(q)) ||
            (c.phone && c.phone.toLowerCase().includes(q))
        );
    });

    return (
        <AdminLayout title="Customers">
            {/* Frontend Search Bar */}
            <div className="bg-white border border-[#E6DFD5] rounded-xl p-4 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
                <div className="flex items-center gap-3 w-full sm:w-auto flex-1">
                    <input
                        type="text"
                        placeholder="Search customer name, email, phone locally..."
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                        className="px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-xs text-stone-900 flex-1 sm:w-80 focus:outline-none focus:border-[#8C6554]"
                    />
                    {search && (
                        <button
                            type="button"
                            onClick={() => setSearch('')}
                            className="text-stone-500 hover:text-[#221F1F] text-xs font-bold uppercase tracking-wider px-2 cursor-pointer"
                        >
                            Clear
                        </button>
                    )}
                </div>
            </div>

            <div className="bg-white border border-[#E6DFD5] rounded-xl overflow-hidden shadow-sm">
                <div className="overflow-x-auto">
                    <table className="w-full text-xs text-stone-700">
                        <thead className="bg-[#F9F6F0] text-stone-500 uppercase border-b border-[#E6DFD5] font-bold">
                            <tr>
                                <th className="p-4">Name</th>
                                <th className="p-4">Email</th>
                                <th className="p-4 text-center">Total Orders</th>
                                <th className="p-4">Last Order</th>
                                <th className="p-4 text-right">View</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-[#E6DFD5]">
                            {filteredCustomers.map(c => (
                                <tr key={c.id} className="hover:bg-[#F9F6F0]/60 transition-colors">
                                    <td className="p-4 font-bold text-[#221F1F]">{c.name}</td>
                                    <td className="p-4 text-stone-600">{c.email}</td>
                                    <td className="p-4 text-center font-bold text-[#8C6554]">{c.orders_count}</td>
                                    <td className="p-4 text-stone-500">
                                        {c.orders?.[0] ? new Date(c.orders[0].created_at).toLocaleDateString() : '—'}
                                    </td>
                                    <td className="p-4 text-right">
                                        <a href={`/admin/customers/${c.id}`} className="text-xs text-[#8C6554] hover:text-[#221F1F] font-bold uppercase tracking-wider">
                                            History →
                                        </a>
                                    </td>
                                </tr>
                            ))}
                            {filteredCustomers.length === 0 && (
                                <tr><td colSpan="5" className="p-8 text-center text-stone-500">No matching customers found.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
