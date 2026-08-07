import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import '../css/app.css';

Alpine.plugin(collapse);
window.Alpine = Alpine;

// Register wishlist store in Alpine
Alpine.store('wishlist', {
    ids: [],
    count: 0,
    init() {
        this.fetchStatus();
    },
    fetchStatus() {
        fetch('/wishlist/status', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                this.ids = data.ids || [];
                this.count = data.count || 0;
            })
            .catch(() => {});
    },
    toggle(productId) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch('/wishlist/toggle/' + productId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (res.status === 401) {
                window.location.href = '/login';
                return;
            }
            return res.json();
        })
        .then(data => {
            if (data && data.success) {
                if (data.wishlisted) {
                    this.ids.push(productId);
                } else {
                    this.ids = this.ids.filter(id => id !== productId);
                }
                this.count = data.count;
                window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message } }));
            }
        })
        .catch(() => {});
    },
    has(productId) {
        return this.ids.includes(productId);
    }
});

// Register miniCart component in Alpine
Alpine.data('miniCart', () => ({
    openDrawer: false,
    navMenuOpen: false,
    searchOpen: false,
    showGlobalSizeGuide: false,
    showPolicyModal: null,
    searchQuery: '',
    cart: { count: 0, items: [], subtotal_formatted: '$0.00' },
    toast: { show: false, message: '', isError: false },
    toastTimeout: null,
    init() {
        this.fetchCart();
    },
    performSearch() {
        if (this.searchQuery && this.searchQuery.trim()) {
            window.location.href = '/catalog?q=' + encodeURIComponent(this.searchQuery.trim());
        }
    },
    showToast(msg, isError = false) {
        this.toast.message = msg;
        this.toast.isError = isError;
        this.toast.show = true;
        if (this.toastTimeout) clearTimeout(this.toastTimeout);
        this.toastTimeout = setTimeout(() => {
            this.toast.show = false;
        }, 3500);
    },
    fetchCart(shouldOpen = false) {
        fetch('/cart/summary', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                this.cart = data;
                if (shouldOpen) this.openDrawer = true;
            })
            .catch(() => {});
    },
    removeItem(itemId) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch('/cart/remove/' + itemId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => { 
            if (data.summary) this.cart = data.summary;
            this.showToast('Item removed from bag');
        });
    }
}));

document.addEventListener('DOMContentLoaded', () => {
    Alpine.start();
});
