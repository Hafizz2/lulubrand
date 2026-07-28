import React from 'react';
import { useForm, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function ScheduleSettings({ pickupTimes, overrides }) {
    const slotForm = useForm({
        time_label: '',
        is_active: true,
    });

    const overrideForm = useForm({
        pickup_time_id: '',
        override_date: '',
        status: 'full',
    });

    const handleCreateSlot = (e) => {
        e.preventDefault();
        slotForm.post('/admin/settings/schedule/slots', {
            onSuccess: () => slotForm.reset(),
        });
    };

    const handleToggleSlot = (slotId) => {
        router.post(`/admin/settings/schedule/slots/${slotId}/toggle`);
    };

    const handleDeleteSlot = (slotId) => {
        if (!confirm('Delete this pickup time slot?')) return;
        router.delete(`/admin/settings/schedule/slots/${slotId}`);
    };

    const handleCreateOverride = (e) => {
        e.preventDefault();
        overrideForm.post('/admin/settings/schedule/overrides', {
            onSuccess: () => overrideForm.reset(),
        });
    };

    const handleDeleteOverride = (overrideId) => {
        router.delete(`/admin/settings/schedule/overrides/${overrideId}`);
    };

    return (
        <AdminLayout title="Schedule & Time Slots Settings">
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-6xl">
                {/* Pickup Time Slots */}
                <div className="bg-white border border-[#E6DFD5] rounded-xl p-6 space-y-6 shadow-sm">
                    <h2 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F] border-b border-[#E6DFD5] pb-3">
                        ⏰ Pickup / Delivery Time Slots
                    </h2>

                    <form onSubmit={handleCreateSlot} className="flex gap-2">
                        <input
                            type="text"
                            placeholder="e.g. 10:00 AM - 12:00 PM"
                            value={slotForm.data.time_label}
                            onChange={e => slotForm.setData('time_label', e.target.value)}
                            className="flex-1 px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            required
                        />
                        <button
                            type="submit"
                            disabled={slotForm.processing}
                            className="px-5 py-2.5 bg-[#8C6554] hover:bg-[#755243] text-white font-bold text-xs uppercase rounded-lg shadow-sm"
                        >
                            Add Slot
                        </button>
                    </form>

                    <div className="space-y-2">
                        {pickupTimes.map(slot => (
                            <div key={slot.id} className="flex items-center justify-between p-3.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-xs">
                                <span className={`font-semibold ${slot.is_active ? 'text-[#221F1F]' : 'text-stone-400 line-through'}`}>{slot.time_label}</span>
                                <div className="flex items-center space-x-3">
                                    <button
                                        onClick={() => handleToggleSlot(slot.id)}
                                        className={`text-[10px] font-bold uppercase px-2.5 py-1 rounded-full border ${
                                            slot.is_active ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-stone-200 text-stone-600 border-stone-300'
                                        }`}
                                    >
                                        {slot.is_active ? 'Active' : 'Disabled'}
                                    </button>
                                    <button
                                        onClick={() => handleDeleteSlot(slot.id)}
                                        className="text-xs text-rose-700 hover:underline font-bold uppercase tracking-wider"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Overrides & Fully Booked Days/Slots */}
                <div className="bg-white border border-[#E6DFD5] rounded-xl p-6 space-y-6 shadow-sm">
                    <h2 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F] border-b border-[#E6DFD5] pb-3">
                        🚫 Fully Booked / Blocked Date Overrides
                    </h2>

                    <form onSubmit={handleCreateOverride} className="space-y-3">
                        <div>
                            <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Select Time Slot *</label>
                            <select
                                value={overrideForm.data.pickup_time_id}
                                onChange={e => overrideForm.setData('pickup_time_id', e.target.value)}
                                className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                required
                            >
                                <option value="">Select Slot...</option>
                                {pickupTimes.map(s => (
                                    <option key={s.id} value={s.id}>{s.time_label}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Date to Block *</label>
                            <input
                                type="date"
                                value={overrideForm.data.override_date}
                                onChange={e => overrideForm.setData('override_date', e.target.value)}
                                className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                required
                            />
                        </div>
                        <button
                            type="submit"
                            disabled={overrideForm.processing}
                            className="w-full py-2.5 bg-[#8C6554] hover:bg-[#755243] text-white font-bold text-xs uppercase rounded-lg shadow-sm"
                        >
                            Block Slot for Date
                        </button>
                    </form>

                    <div className="space-y-2 pt-2">
                        {overrides.map(o => (
                            <div key={o.id} className="flex items-center justify-between p-3.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-xs">
                                <div>
                                    <span className="font-bold text-[#221F1F] block">{o.override_date}</span>
                                    <span className="text-[11px] text-stone-500">{o.pickup_time?.time_label}</span>
                                </div>
                                <button
                                    onClick={() => handleDeleteOverride(o.id)}
                                    className="text-xs text-rose-700 hover:underline font-bold uppercase tracking-wider"
                                >
                                    Unblock
                                </button>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
