import React from 'react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Index({ customers }) {
    return (
        <AdminLayout title="Customers">
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
                            {customers.data.map(c => (
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
                            {customers.data.length === 0 && (
                                <tr><td colSpan="5" className="p-8 text-center text-stone-500">No registered customers yet.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
