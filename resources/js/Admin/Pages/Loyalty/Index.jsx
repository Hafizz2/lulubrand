import React, { useState, useEffect } from 'react';
import AdminLayout from '@/Admin/Layouts/AdminLayout';
import { router } from '@inertiajs/react';

export default function LoyaltyIndex({ settings, recentTransactions = [] }) {
    const [phone, setPhone] = useState('');
    const [customers, setCustomers] = useState([]);
    const [selected, setSelected] = useState(null);
    const [customerDetail, setCustomerDetail] = useState(null);
    const [purchaseAmount, setPurchaseAmount] = useState('');
    const [redeemPoints, setRedeemPoints] = useState('');
    const [loading, setLoading] = useState(false);
    const [redeemLoading, setRedeemLoading] = useState(false);
    const [searchLoading, setSearchLoading] = useState(false);
    
    // Debounced search
    useEffect(() => {
        if (phone.length < 4) { 
            setCustomers([]); 
            return; 
        }
        const timer = setTimeout(() => {
            setSearchLoading(true);
            fetch(`/admin/loyalty/search?phone=${encodeURIComponent(phone)}`)
                .then(r => r.json())
                .then(data => { 
                    setCustomers(data.customers || []); 
                    setSearchLoading(false); 
                })
                .catch(() => {
                    setSearchLoading(false);
                });
        }, 300);
        return () => clearTimeout(timer);
    }, [phone]);
    
    // Load customer detail when selected
    const selectCustomer = (customer) => {
        setSelected(customer);
        setCustomerDetail(null);
        fetch(`/admin/loyalty/customer/${customer.id}`)
            .then(r => r.json())
            .then(data => setCustomerDetail(data))
            .catch(err => console.error("Error loading customer detail", err));
    };
    
    // Calculate preview points
    const previewPoints = purchaseAmount && settings.birr_per_point > 0
        ? Math.floor(parseFloat(purchaseAmount) / settings.birr_per_point)
        : 0;

    // Calculate preview redeem discount
    const previewDiscount = redeemPoints && settings.point_value_cents > 0
        ? (parseInt(redeemPoints) * settings.point_value_cents) / 100
        : 0;
    
    // Award points
    const handleAward = (e) => {
        e.preventDefault();
        if (!selected || !purchaseAmount) return;
        setLoading(true);
        router.post('/admin/loyalty/award', {
            user_id: selected.id,
            purchase_amount: parseFloat(purchaseAmount),
        }, {
            onSuccess: () => {
                setPurchaseAmount('');
                setLoading(false);
                selectCustomer(selected); // Refresh
            },
            onError: () => {
                setLoading(false);
            }
        });
    };

    // Redeem points
    const handleRedeem = (e) => {
        e.preventDefault();
        if (!selected || !redeemPoints) return;
        setRedeemLoading(true);
        router.post('/admin/loyalty/redeem', {
            user_id: selected.id,
            points: parseInt(redeemPoints),
        }, {
            onSuccess: () => {
                setRedeemPoints('');
                setRedeemLoading(false);
                selectCustomer(selected); // Refresh
            },
            onError: () => {
                setRedeemLoading(false);
            }
        });
    };
    
    return (
        <AdminLayout title="Loyalty Program">
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {/* Search Panel */}
                <div className="lg:col-span-1 space-y-4">
                    <div className="bg-white border border-stone-200 rounded-xl shadow-sm p-5">
                        <label className="block text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">
                            Search Customer
                        </label>
                        <div className="relative">
                            <input
                                type="text"
                                className="w-full pl-10 pr-4 py-3 bg-[#F9F6F0] border-0 rounded-lg focus:ring-2 focus:ring-[#8C6554] text-sm font-semibold"
                                placeholder="Enter phone number..."
                                value={phone}
                                onChange={(e) => setPhone(e.target.value)}
                            />
                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg className="w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            {searchLoading && (
                                <div className="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <div className="w-4 h-4 border-2 border-[#8C6554] border-t-transparent rounded-full animate-spin"></div>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Results */}
                    {customers.length > 0 && (
                        <div className="bg-white border border-stone-200 rounded-xl shadow-sm overflow-hidden">
                            <ul className="divide-y divide-stone-100 max-h-[400px] overflow-y-auto">
                                {customers.map(c => (
                                    <li key={c.id}>
                                        <button 
                                            onClick={() => selectCustomer(c)}
                                            className={`w-full text-left px-5 py-4 hover:bg-[#FAF8F5] transition-colors flex justify-between items-center ${selected?.id === c.id ? 'bg-[#FAF8F5] border-l-4 border-[#8C6554]' : 'border-l-4 border-transparent'}`}
                                        >
                                            <div>
                                                <div className="text-sm font-bold text-[#221F1F]">{c.name}</div>
                                                <div className="text-xs text-stone-500 font-medium">{c.phone}</div>
                                            </div>
                                            <div className="text-right">
                                                <div className="text-sm font-bold text-[#8C6554]">{c.points_balance}</div>
                                                <div className="text-[10px] uppercase tracking-wider text-stone-400">Pts</div>
                                            </div>
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    {phone.length >= 4 && customers.length === 0 && !searchLoading && (
                        <div className="bg-white border border-stone-200 rounded-xl shadow-sm p-8 text-center text-stone-500 text-sm">
                            No customers found.
                        </div>
                    )}

                    {/* Recent Shop Activity */}
                    <div className="bg-white border border-stone-200 rounded-xl shadow-sm p-5 space-y-4">
                        <h3 className="text-xs font-bold uppercase tracking-wider text-stone-500 border-b border-stone-100 pb-2">
                            Recent Shop Activity
                        </h3>
                        {recentTransactions && recentTransactions.length > 0 ? (
                            <div className="space-y-3 max-h-[300px] overflow-y-auto pr-1">
                                {recentTransactions.map((tx, idx) => (
                                    <div key={idx} className="flex justify-between items-start text-xs border-b border-stone-50 pb-2 last:border-0 last:pb-0">
                                        <div className="flex-1 mr-2">
                                            <span className="font-bold text-[#221F1F] block">{tx.customer_name}</span>
                                            <span className="text-[10px] text-stone-400 font-mono block mt-0.5">{tx.customer_phone}</span>
                                            <span className="text-[9px] text-stone-500 font-light block mt-1 line-clamp-1 leading-tight">{tx.description}</span>
                                        </div>
                                        <div className="text-right flex-shrink-0">
                                            <span className={`inline-block px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider ${
                                                tx.type === 'earn' ? 'bg-emerald-100 text-emerald-800' : 
                                                tx.type === 'redeem' ? 'bg-rose-100 text-rose-800' : 'bg-stone-100 text-stone-800'
                                            }`}>
                                                {tx.type}
                                            </span>
                                            <span className={`block font-extrabold text-[11px] mt-1 ${tx.points > 0 ? 'text-emerald-700' : 'text-rose-700'}`}>
                                                {tx.points > 0 ? `+${tx.points}` : tx.points} pts
                                            </span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-stone-400 text-xs py-2 text-center">No recent point transactions.</p>
                        )}
                    </div>
                </div>

                {/* Detail Panel */}
                <div className="lg:col-span-2">
                    {selected ? (
                        <div className="space-y-6">
                            {/* Customer Profile Card */}
                            <div className="bg-white border border-stone-200 rounded-xl shadow-sm p-6">
                                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-stone-100 pb-6 mb-6">
                                    <div>
                                        <h2 className="text-xl font-bold text-[#221F1F]">{selected.name}</h2>
                                        <div className="flex items-center space-x-4 mt-1 text-sm text-stone-500 font-medium">
                                            <span>{selected.phone}</span>
                                            {selected.email && (
                                                <>
                                                    <span className="w-1 h-1 rounded-full bg-stone-300"></span>
                                                    <span>{selected.email}</span>
                                                </>
                                            )}
                                        </div>
                                    </div>
                                    
                                    <div className="flex gap-4">
                                        <div className="bg-[#FAF8F5] px-4 py-2 rounded-lg text-center">
                                            <div className="text-xl font-bold text-[#8C6554]">
                                                {customerDetail ? customerDetail.points_balance : selected.points_balance}
                                            </div>
                                            <div className="text-[10px] uppercase tracking-wider text-stone-500 font-bold">Current</div>
                                        </div>
                                        <div className="bg-[#FAF8F5] px-4 py-2 rounded-lg text-center">
                                            <div className="text-xl font-bold text-emerald-600">
                                                {customerDetail ? customerDetail.lifetime_earned : '-'}
                                            </div>
                                            <div className="text-[10px] uppercase tracking-wider text-stone-500 font-bold">Earned</div>
                                        </div>
                                        <div className="bg-[#FAF8F5] px-4 py-2 rounded-lg text-center">
                                            <div className="text-xl font-bold text-rose-600">
                                                {customerDetail ? customerDetail.lifetime_redeemed : '-'}
                                            </div>
                                            <div className="text-[10px] uppercase tracking-wider text-stone-500 font-bold">Redeemed</div>
                                        </div>
                                    </div>
                                </div>

                                {/* Actions: Award & Redeem Forms */}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    
                                    {/* Award Points Form */}
                                    <form onSubmit={handleAward} className="bg-[#FAF8F5] rounded-xl p-5 border border-stone-100 flex flex-col justify-between">
                                        <div>
                                            <h3 className="text-xs font-bold uppercase tracking-wider text-stone-500 mb-3">Add In-Shop Purchase</h3>
                                            <div className="space-y-3">
                                                <div>
                                                    <label className="block text-[11px] font-bold text-[#221F1F] mb-1">Purchase Amount (Birr)</label>
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        step="0.01"
                                                        required
                                                        className="w-full bg-white border border-stone-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#8C6554] focus:border-transparent outline-none"
                                                        value={purchaseAmount}
                                                        onChange={(e) => setPurchaseAmount(e.target.value)}
                                                    />
                                                </div>
                                                <div className="px-4 py-2 bg-white border border-stone-200 rounded-lg flex items-center justify-between text-xs">
                                                    <span className="font-semibold text-stone-500">Will Earn:</span>
                                                    <span className="font-extrabold text-[#8C6554] text-sm">+{previewPoints} PTS</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button
                                            type="submit"
                                            disabled={loading || !purchaseAmount || purchaseAmount <= 0}
                                            className="w-full mt-4 bg-[#8C6554] hover:bg-[#7A5747] text-white text-xs font-bold uppercase tracking-wider py-2.5 rounded-lg transition-colors disabled:opacity-50 cursor-pointer"
                                        >
                                            {loading ? 'Processing...' : 'Award Points'}
                                        </button>
                                    </form>

                                    {/* Redeem Points Form */}
                                    <form onSubmit={handleRedeem} className="bg-[#FAF8F5] rounded-xl p-5 border border-stone-100 flex flex-col justify-between">
                                        <div>
                                            <h3 className="text-xs font-bold uppercase tracking-wider text-rose-800 mb-3">Redeem Customer Points</h3>
                                            <div className="space-y-3">
                                                <div>
                                                    <label className="block text-[11px] font-bold text-[#221F1F] mb-1">Points to Redeem (Min: {settings.min_redeem})</label>
                                                    <input
                                                        type="number"
                                                        min={settings.min_redeem}
                                                        step="1"
                                                        required
                                                        className="w-full bg-white border border-stone-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-rose-800 focus:border-transparent outline-none"
                                                        value={redeemPoints}
                                                        onChange={(e) => setRedeemPoints(e.target.value)}
                                                    />
                                                </div>
                                                <div className="px-4 py-2 bg-white border border-stone-200 rounded-lg flex items-center justify-between text-xs">
                                                    <span className="font-semibold text-stone-500">Discount Value:</span>
                                                    <span className="font-extrabold text-rose-800 text-sm">-{previewDiscount} Birr</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button
                                            type="submit"
                                            disabled={redeemLoading || !redeemPoints || parseInt(redeemPoints) < settings.min_redeem || parseInt(redeemPoints) > (customerDetail ? customerDetail.points_balance : selected.points_balance)}
                                            className="w-full mt-4 bg-rose-800 hover:bg-rose-900 text-white text-xs font-bold uppercase tracking-wider py-2.5 rounded-lg transition-colors disabled:opacity-50 cursor-pointer"
                                        >
                                            {redeemLoading ? 'Processing...' : 'Redeem Points'}
                                        </button>
                                    </form>

                                </div>
                            </div>

                            {/* Transaction History */}
                            <div className="bg-white border border-stone-200 rounded-xl shadow-sm p-6">
                                <h3 className="text-sm font-bold text-[#221F1F] mb-4">Recent Transactions</h3>
                                
                                {!customerDetail ? (
                                    <div className="animate-pulse space-y-3">
                                        <div className="h-10 bg-stone-100 rounded"></div>
                                        <div className="h-10 bg-stone-100 rounded"></div>
                                        <div className="h-10 bg-stone-100 rounded"></div>
                                    </div>
                                ) : customerDetail.transactions && customerDetail.transactions.length > 0 ? (
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-left border-collapse">
                                            <thead>
                                                <tr>
                                                    <th className="border-b border-stone-100 py-3 text-[10px] uppercase tracking-wider font-bold text-stone-400">Date</th>
                                                    <th className="border-b border-stone-100 py-3 text-[10px] uppercase tracking-wider font-bold text-stone-400">Type</th>
                                                    <th className="border-b border-stone-100 py-3 text-[10px] uppercase tracking-wider font-bold text-stone-400">Points</th>
                                                    <th className="border-b border-stone-100 py-3 text-[10px] uppercase tracking-wider font-bold text-stone-400">Description</th>
                                                </tr>
                                            </thead>
                                            <tbody className="text-sm">
                                                {customerDetail.transactions.map((tx, idx) => (
                                                    <tr key={idx} className="border-b border-stone-50 last:border-0 hover:bg-[#FAF8F5] transition-colors">
                                                        <td className="py-3 text-stone-600 whitespace-nowrap">
                                                            {new Date(tx.created_at).toLocaleDateString()}
                                                        </td>
                                                        <td className="py-3">
                                                            <span className={`inline-block px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider ${
                                                                tx.type === 'earn' ? 'bg-emerald-100 text-emerald-800' : 
                                                                tx.type === 'redeem' ? 'bg-rose-100 text-rose-800' : 'bg-stone-100 text-stone-800'
                                                            }`}>
                                                                {tx.type}
                                                            </span>
                                                        </td>
                                                        <td className={`py-3 font-bold ${tx.points > 0 ? 'text-emerald-600' : 'text-rose-600'}`}>
                                                            {tx.points > 0 ? '+' : ''}{tx.points}
                                                        </td>
                                                        <td className="py-3 text-stone-500">
                                                            {tx.description}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <div className="text-center py-6 text-sm text-stone-500">
                                        No transactions yet.
                                    </div>
                                )}
                            </div>
                        </div>
                    ) : (
                        <div className="h-full min-h-[400px] flex flex-col items-center justify-center bg-white border border-stone-200 rounded-xl shadow-sm p-8 text-center">
                            <div className="w-16 h-16 bg-[#FAF8F5] rounded-full flex items-center justify-center mb-4 text-[#8C6554]">
                                <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 className="text-lg font-bold text-[#221F1F] mb-2">Select a Customer</h3>
                            <p className="text-sm text-stone-500 max-w-sm">
                                Search for a customer by phone number to view their details, transaction history, and award points.
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
