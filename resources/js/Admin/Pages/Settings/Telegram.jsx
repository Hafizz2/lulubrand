import React, { useState } from 'react';
import { useForm, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

function Section({ title, children }) {
    return (
        <div className="bg-white border border-[#E6DFD5] rounded-xl p-6 space-y-4 shadow-sm">
            <h2 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F] border-b border-[#E6DFD5] pb-3">
                {title}
            </h2>
            {children}
        </div>
    );
}

function InfoRow({ label, value, mono = false }) {
    return (
        <div className="flex items-center justify-between py-2 border-b border-[#E6DFD5]/60 last:border-0">
            <span className="text-xs text-stone-500 font-semibold uppercase tracking-wider">{label}</span>
            <span className={`text-xs text-[#221F1F] ${mono ? 'font-mono' : 'font-semibold'}`}>{value ?? '—'}</span>
        </div>
    );
}

export default function Telegram({ configured, webhookInfo, botInfo, ownerChatId, appUrl }) {
    const [broadcastText, setBroadcastText] = useState('');
    const [broadcastSent, setBroadcastSent] = useState(false);

    const webhookForm = useForm({ webhook_url: '' });
    const previewForm = useForm({ message: '' });
    const broadcastForm = useForm({ message: '' });

    const handleSetWebhook = (e) => {
        e.preventDefault();
        webhookForm.post('/admin/settings/telegram/webhook');
    };

    const handleDeleteWebhook = () => {
        if (!confirm('Clear the current Telegram webhook?')) return;
        router.delete('/admin/settings/telegram/webhook');
    };

    const handlePreview = (e) => {
        e.preventDefault();
        previewForm.setData('message', broadcastText);
        previewForm.post('/admin/settings/telegram/preview');
    };

    const handleBroadcast = (e) => {
        e.preventDefault();
        if (!confirm('Broadcast this message to ALL subscribers now?')) return;
        broadcastForm.setData('message', broadcastText);
        broadcastForm.post('/admin/settings/telegram/broadcast', {
            onSuccess: () => { setBroadcastSent(true); setBroadcastText(''); }
        });
    };

    const suggestedWebhookUrl = `${appUrl}/telegram/webhook`;

    return (
        <AdminLayout title="Telegram Integration">
            {/* Status Banner */}
            <div className={`mb-6 p-4 rounded-xl border flex items-center space-x-3 ${configured ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-800' : 'bg-amber-500/10 border-amber-500/30 text-amber-800'}`}>
                <div className={`w-2.5 h-2.5 rounded-full flex-shrink-0 ${configured ? 'bg-emerald-600' : 'bg-amber-600'} animate-pulse`} />
                <div>
                    <p className="text-sm font-bold">
                        {configured ? 'Bot Token Configured' : 'Bot Token Not Set'}
                    </p>
                    <p className="text-xs opacity-80">
                        {configured
                            ? `@${botInfo?.result?.username || 'your-bot'} is active.`
                            : 'Add TELEGRAM_BOT_TOKEN and TELEGRAM_OWNER_CHAT_ID to your .env file.'}
                    </p>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Bot Info */}
                <Section title="🤖 Bot Information">
                    <InfoRow label="Username" value={botInfo?.result?.username ? `@${botInfo.result.username}` : 'Not configured'} />
                    <InfoRow label="Bot Name" value={botInfo?.result?.first_name} />
                    <InfoRow label="Owner Chat ID" value={ownerChatId} mono />
                    <div className="pt-2 text-xs text-stone-500 leading-relaxed">
                        To test locally, use <code>php artisan telegram:poll</code> command.
                    </div>
                </Section>

                {/* Webhook Setup */}
                <Section title="🔗 Webhook Configuration">
                    <InfoRow label="Webhook URL" value={webhookInfo?.url} mono />
                    <InfoRow label="Pending Updates" value={webhookInfo?.pending_update_count ?? 0} />
                    <InfoRow label="Last Error" value={webhookInfo?.last_error_message} />

                    <form onSubmit={handleSetWebhook} className="space-y-3 pt-2">
                        <div>
                            <label className="block text-[10px] font-semibold uppercase text-stone-600 mb-1">Set Webhook URL</label>
                            <input
                                type="url"
                                value={webhookForm.data.webhook_url}
                                onChange={e => webhookForm.setData('webhook_url', e.target.value)}
                                placeholder={suggestedWebhookUrl}
                                className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                        </div>
                        <div className="flex space-x-2">
                            <button
                                type="submit"
                                disabled={webhookForm.processing}
                                className="px-5 py-2.5 bg-[#8C6554] hover:bg-[#755243] text-white font-bold text-xs uppercase rounded-lg shadow-sm"
                            >
                                Set Webhook
                            </button>
                            {webhookInfo?.url && (
                                <button
                                    type="button"
                                    onClick={handleDeleteWebhook}
                                    className="px-5 py-2.5 bg-rose-700 hover:bg-rose-800 text-white font-bold text-xs uppercase rounded-lg shadow-sm"
                                >
                                    Remove
                                </button>
                            )}
                        </div>
                    </form>
                </Section>
            </div>

            {/* Customer Commands Reference */}
            <div className="mt-6">
                <Section title="🤖 Customer Bot Commands">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        {[
                            { cmd: '/start <phone>', desc: 'Link account by phone number' },
                            { cmd: '/start <order#>', desc: 'Link account by order number (e.g. LULU-ABC123)' },
                            { cmd: '/track <order#>', desc: 'Get live order status' },
                            { cmd: '/orders', desc: 'List last 5 orders for linked account' },
                            { cmd: '/unlink', desc: 'Remove this chat from notifications' },
                        ].map(({ cmd, desc }) => (
                            <div key={cmd} className="flex items-start space-x-3 p-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg">
                                <code className="text-[#8C6554] font-mono text-[11px] font-bold flex-shrink-0">{cmd}</code>
                                <span className="text-stone-600">{desc}</span>
                            </div>
                        ))}
                    </div>
                </Section>
            </div>
        </AdminLayout>
    );
}
