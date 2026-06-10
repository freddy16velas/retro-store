/* ============================================================
   RetroVault — JavaScript
   ============================================================ */

'use strict';

// ── Cart AJAX ─────────────────────────────────────────────────
function addToCart(productId, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> CARGANDO...';

    fetch('actions/cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add&producto_id=${productId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="bi bi-check-lg"></i> AGREGADO';
            btn.style.background = 'var(--neon-green)';
            updateCartBadge(data.cart_count);
            showToast(data.message, 'success');
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-cart-plus"></i> AGREGAR AL CARRITO';
                btn.style.background = '';
            }, 1500);
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cart-plus"></i> AGREGAR AL CARRITO';
            showToast(data.message, 'error');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cart-plus"></i> AGREGAR AL CARRITO';
        showToast('Error de conexión', 'error');
    });
}

function updateCartBadge(count) {
    let badge = document.querySelector('.cart-badge');
    const cartBtn = document.querySelector('.retro-cart-btn');
    if (!cartBtn) return;
    if (count > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'cart-badge';
            cartBtn.appendChild(badge);
        }
        badge.textContent = count;
    } else if (badge) {
        badge.remove();
    }
}

function updateCartQty(carritoId, qty) {
    fetch('actions/cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update&carrito_id=${carritoId}&cantidad=${qty}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            updateCartTotals(data);
            updateCartBadge(data.cart_count);
        }
    });
}

function updateCartTotals(data) {
    if (data.subtotal !== undefined) {
        const el = document.querySelector(`[data-subtotal="${data.carrito_id}"]`);
        if (el) el.textContent = 'Q ' + parseFloat(data.subtotal).toFixed(2);
    }
    const totalEl = document.querySelector('.cart-total-amount');
    if (totalEl && data.total !== undefined) {
        totalEl.textContent = 'Q ' + parseFloat(data.total).toFixed(2);
    }
}

function removeFromCart(carritoId) {
    if (!confirm('¿Eliminar este producto del carrito?')) return;
    fetch('actions/cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&carrito_id=${carritoId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const row = document.querySelector(`[data-row="${carritoId}"]`);
            if (row) {
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                row.style.transition = '.3s';
                setTimeout(() => { row.remove(); checkEmptyCart(); }, 300);
            }
            updateCartBadge(data.cart_count);
            const totalEl = document.querySelector('.cart-total-amount');
            if (totalEl) totalEl.textContent = 'Q ' + parseFloat(data.total).toFixed(2);
        }
    });
}

function checkEmptyCart() {
    const rows = document.querySelectorAll('[data-row]');
    if (rows.length === 0) {
        location.reload();
    }
}

// ── Toast notifications ───────────────────────────────────────
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:10000;display:flex;flex-direction:column;gap:8px;';
        document.body.appendChild(container);
    }
    const color = type === 'success' ? 'var(--neon-green)' : 'var(--neon-pink)';
    const icon  = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle';
    const toast = document.createElement('div');
    toast.style.cssText = `
        background:var(--card-bg);
        border-left:3px solid ${color};
        color:${color};
        padding:.8rem 1.2rem;
        font-family:var(--font-pixel);
        font-size:.45rem;
        letter-spacing:.06em;
        box-shadow:0 0 20px rgba(0,0,0,.5);
        opacity:0;
        transform:translateX(20px);
        transition:.3s ease;
        max-width:300px;
    `;
    toast.innerHTML = `<i class="bi ${icon}"></i> ${message}`;
    container.appendChild(toast);
    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    });
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ── Product search filter ─────────────────────────────────────
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.product-item').forEach(item => {
            const name = item.dataset.name || '';
            const cat  = item.dataset.cat  || '';
            item.style.display = (name.includes(q) || cat.includes(q)) ? '' : 'none';
        });
    });
}

// ── Category filter buttons ───────────────────────────────────
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const cat = this.dataset.cat;
        document.querySelectorAll('.product-item').forEach(item => {
            item.style.display = (cat === 'all' || item.dataset.cat === cat) ? '' : 'none';
        });
    });
});

// ── Glitch effect on hover for section titles ─────────────────
document.querySelectorAll('.section-title, .hero-title').forEach(el => {
    el.addEventListener('mouseenter', () => {
        el.style.animation = 'glitchText .3s steps(2) 1';
        el.addEventListener('animationend', () => el.style.animation = '', { once: true });
    });
});

// Add glitch CSS dynamically
const style = document.createElement('style');
style.textContent = `
@keyframes glitchText {
    0%   { text-shadow: 2px 0 var(--neon-pink), -2px 0 var(--neon-cyan); }
    33%  { text-shadow: -2px 0 var(--neon-pink),  2px 0 var(--neon-cyan); transform: skewX(1deg); }
    66%  { text-shadow: 2px 0 var(--neon-cyan), -2px 0 var(--neon-green); transform: skewX(-1deg); }
    100% { text-shadow: none; transform: none; }
}`;
document.head.appendChild(style);
