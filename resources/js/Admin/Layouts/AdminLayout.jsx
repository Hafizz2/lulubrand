import React, { useState } from 'react';
import { usePage, router } from '@inertiajs/react';

const mainNavItems = [
    {
        label: 'Dashboard',
        href: '/admin',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001 1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
        ),
    },
    {
        label: 'Products',
        href: '/admin/products',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
        ),
    },
    {
        label: 'Outfits',
        href: '/admin/outfits',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
        ),
    },
    {
        label: 'Categories',
        href: '/admin/categories',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
        ),
    },
    {
        label: 'Hero Banners',
        href: '/admin/hero-banners',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        ),
    },
    {
        label: 'Orders',
        href: '/admin/orders',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
        ),
    },
    {
        label: 'Stock',
        href: '/admin/stock',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
        ),
    },
    {
        label: 'Customers',
        href: '/admin/customers',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        ),
    },
    {
        label: 'Loyalty',
        href: '/admin/loyalty',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        ),
    },
    {
        label: 'Discounts',
        href: '/admin/discounts',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M7 7h.01M17 17h.01M3 3l18 18M9.5 9.5a2.5 2.5 0 113.535 3.536M14.5 14.5a2.5 2.5 0 11-3.536-3.536" />
            </svg>
        ),
    },
];

const settingsNavItems = [
    {
        label: 'User & Staff',
        href: '/admin/users',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        ),
    },
    {
        label: 'Checkout Settings',
        href: '/admin/settings/checkout',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2z" />
            </svg>
        ),
    },
    {
        label: 'Delivery Rates',
        href: '/admin/settings/delivery',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.5m1.832-3.752a9 9 0 11-12.013-4.723" />
            </svg>
        ),
    },
    {
        label: 'Bank Accounts',
        href: '/admin/settings/banks',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
            </svg>
        ),
    },
    {
        label: 'Schedule & Slots',
        href: '/admin/settings/schedule',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        ),
    },
    {
        label: 'Size Guide',
        href: '/admin/settings/size-guide',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M3 6l3 18h12l3-18H3zm3 0h12M9 10v4m6-4v4" />
            </svg>
        ),
    },
    {
        label: 'Content & Policies',
        href: '/admin/settings/content',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        ),
    },
    {
        label: 'Telegram Bot',
        href: '/admin/settings/telegram',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
        ),
    },
    {
        label: 'Push Broadcast',
        href: '/admin/push-broadcast',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
        ),
    },
    {
        label: 'Loyalty',
        href: '/admin/settings/loyalty',
        icon: (
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
            </svg>
        ),
    },
];

export default function AdminLayout({ children, title = 'Admin Console' }) {
    const { auth, flash } = usePage().props;
    const user = auth?.user;
    const [mobileOpen, setMobileOpen] = useState(false);

    const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';

    const handleLogout = (e) => {
        e.preventDefault();
        router.post('/admin/logout');
    };

    const NavLink = ({ item }) => {
        const active = currentPath === item.href || (item.href !== '/admin' && currentPath.startsWith(item.href));

        return (
            <a
                href={item.href}
                className={`flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all ${
                    active
                        ? 'bg-[#8C6554] text-white shadow-md'
                        : 'text-stone-600 hover:text-[#221F1F] hover:bg-[#F3EEE8]'
                }`}
            >
                <span className={active ? 'text-white' : 'text-stone-400'}>{item.icon}</span>
                <span>{item.label}</span>
            </a>
        );
    };

    const SidebarContent = () => (
        <div className="flex flex-col h-full bg-[#FAF8F5]">
            {/* Logo */}
            <div className="px-5 py-6 border-b border-[#E6DFD5]">
                <div className="flex items-center justify-between">
                    <img src="/logo.png" alt="LULU Admin" className="h-10 w-auto object-contain" />
                    <span className="text-[9px] bg-[#8C6554]/15 text-[#8C6554] px-2.5 py-1 rounded-full border border-[#8C6554]/30 font-bold uppercase tracking-widest">ADMIN</span>
                </div>
            </div>

            {/* Nav */}
            <nav className="flex-1 p-4 space-y-6 overflow-y-auto">
                <div>
                    <span className="text-[10px] font-bold uppercase tracking-[0.2em] text-[#A38B7E] px-3 block mb-2">
                        Core Store
                    </span>
                    <div className="space-y-1">
                        {mainNavItems
                            .filter(item => {
                                if (user?.role === 'cashier') {
                                    return item.label === 'Loyalty';
                                }
                                return true;
                            })
                            .map(item => <NavLink key={item.href} item={item} />)
                        }
                    </div>
                </div>

                {user?.role === 'owner' && (
                    <div>
                        <span className="text-[10px] font-bold uppercase tracking-[0.2em] text-[#A38B7E] px-3 block mb-2">
                            Management & Settings
                        </span>
                        <div className="space-y-1">
                            {settingsNavItems.map(item => <NavLink key={item.href} item={item} />)}
                        </div>
                    </div>
                )}
            </nav>

            {/* User footer */}
            {user && (
                <div className="p-4 border-t border-[#E6DFD5] space-y-3 bg-[#F3EEE8]">
                    <div className="flex items-center space-x-3">
                        <div className="w-8 h-8 rounded-full bg-[#8C6554] text-white flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                            {user.name?.charAt(0)}
                        </div>
                        <div className="flex-1 min-w-0">
                            <p className="text-xs font-bold text-[#221F1F] truncate">{user.name}</p>
                            <span className="text-[10px] uppercase font-bold text-[#8C6554]">
                                {user.role}
                            </span>
                        </div>
                    </div>
                    <button
                        onClick={handleLogout}
                        className="w-full text-left text-xs text-stone-500 hover:text-[#8C6554] transition-colors flex items-center space-x-2 font-semibold uppercase tracking-wider"
                    >
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Sign Out</span>
                    </button>
                </div>
            )}
        </div>
    );

    return (
        <div className="min-h-screen bg-[#F9F6F0] text-[#221F1F] flex selection:bg-[#8C6554] selection:text-white">
            {/* Desktop Sidebar */}
            <aside className="hidden md:flex md:flex-col w-64 bg-[#FAF8F5] border-r border-[#E6DFD5] flex-shrink-0 fixed inset-y-0 left-0 z-30 shadow-sm">
                <SidebarContent />
            </aside>

            {/* Mobile Overlay */}
            {mobileOpen && (
                <div className="fixed inset-0 z-40 md:hidden">
                    <div className="absolute inset-0 bg-stone-950/60 backdrop-blur-sm" onClick={() => setMobileOpen(false)} />
                    <aside className="absolute left-0 inset-y-0 w-64 bg-[#FAF8F5] border-r border-[#E6DFD5] z-50">
                        <SidebarContent />
                    </aside>
                </div>
            )}

            {/* Main Content */}
            <div className="flex-1 md:ml-64 flex flex-col">
                {/* Mobile Top Bar */}
                <div className="md:hidden flex items-center justify-between bg-[#FAF8F5] border-b border-[#E6DFD5] px-4 py-3">
                    <img src="/logo.png" alt="LULU Admin" className="h-8 w-auto object-contain" />
                    <button onClick={() => setMobileOpen(true)} className="text-[#221F1F] p-1">
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

                <main className="flex-1 p-4 md:p-8 overflow-y-auto">
                    <div className="max-w-7xl mx-auto">
                        {/* Flash Messages */}
                        {flash?.error && (
                            <div className="mb-6 p-4 rounded-2xl bg-[#C49A9A]/15 border border-[#C49A9A]/30 text-[#221F1F] text-xs font-semibold">
                                {flash.error}
                            </div>
                        )}
                        {flash?.success && (
                            <div className="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-800 text-xs font-semibold">
                                {flash.success}
                            </div>
                        )}

                        {/* Page Title */}
                        <div className="mb-6 flex items-center justify-between border-b border-[#E6DFD5] pb-4">
                            <h1 className="text-2xl font-serif font-bold tracking-wide uppercase text-[#221F1F]">{title}</h1>
                        </div>

                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
