import Alpine from 'alpinejs';
import Swal from 'sweetalert2';

window.Alpine = Alpine;
window.Swal = Swal;

// SweetAlert2 con la paleta de la marca.
const brand = Swal.mixin({
    confirmButtonColor: '#1f3070',
    cancelButtonColor: '#8a857a',
    confirmButtonText: 'Aceptar',
    cancelButtonText: 'Cancelar',
    background: '#fbfaf7',
    color: '#1b1b18',
    customClass: { popup: 'swal-brand' },
    showClass: { popup: 'swal-in' },
    hideClass: { popup: 'swal-out' },
});

// Alertas de éxito/error enviadas desde el servidor (equivalente al modal de lab1).
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('flash-alert');
    if (el) {
        const { icon, title, text } = el.dataset;
        brand.fire({ icon, title, text, timer: icon === 'success' ? 4000 : undefined, timerProgressBar: true });
    }

    // Formularios con data-confirm piden confirmación antes de enviarse.
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            brand.fire({
                title: form.dataset.confirmTitle || 'Confirmación',
                text: form.dataset.confirm || '¿Estás seguro?',
                icon: 'warning',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: form.dataset.confirmButton || 'Sí, continuar',
            }).then((r) => r.isConfirmed && form.submit());
        });
    });
});

// Panel con pestañas: el indicador se desliza hasta la pestaña activa.
Alpine.data('dash', (initial) => ({
    tab: initial,
    pill: { x: 0, w: 0 },
    ready: false,
    init() {
        this.$nextTick(() => {
            this.move();
            requestAnimationFrame(() => (this.ready = true));
        });
        window.addEventListener('resize', () => this.move());
    },
    set(tab) {
        this.tab = tab;
        this.move();
        history.replaceState(null, '', '?tab=' + tab);
    },
    move() {
        const el = this.$refs['tab_' + this.tab];
        if (el) {
            this.pill.x = el.offsetLeft;
            this.pill.w = el.offsetWidth;
        }
    },
}));

// Formulario de reserva: total estimado en vivo (el servidor lo recalcula).
Alpine.data('booking', (prices, room) => ({
    prices,
    room,
    a: '',
    b: '',
    init() {
        const q = new URLSearchParams(location.search);
        if (q.get('check_in')) this.a = q.get('check_in');
        if (q.get('check_out')) this.b = q.get('check_out');
    },
    get nights() {
        return this.a && this.b ? Math.max(0, Math.round((new Date(this.b) - new Date(this.a)) / 86400000)) : 0;
    },
    get rate() {
        return Number(this.prices[this.room] || 0);
    },
    get total() {
        return this.nights * this.rate;
    },
    money(n) {
        return '$' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
}));

Alpine.start();
// Revelado de secciones al entrar en pantalla.
document.addEventListener('DOMContentLoaded', () => {
    const items = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window)) return items.forEach((el) => el.classList.add('is-in'));
    const io = new IntersectionObserver((entries) => entries.forEach((e) => {
        if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
    }), { threshold: 0.15 });
    items.forEach((el) => io.observe(el));

    // Contadores animados de las cifras.
    const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const co = new IntersectionObserver((entries) => entries.forEach((e) => {
        if (!e.isIntersecting) return;
        co.unobserve(e.target);
        const end = +e.target.dataset.count, t0 = performance.now();
        const tick = (t) => {
            const p = Math.min((t - t0) / 1200, 1);
            e.target.textContent = Math.round(end * (1 - Math.pow(1 - p, 4)));
            if (p < 1) requestAnimationFrame(tick);
        };
        if (!reduce && end > 0) requestAnimationFrame(tick);
    }), { threshold: 0.6 });
    document.querySelectorAll('[data-count]').forEach((el) => co.observe(el));

    // Barra de progreso y resaltado del enlace activo del menú.
    const bar = document.getElementById('progress');
    const links = [...document.querySelectorAll('[data-spy]')];
    const secs = links.map((l) => document.querySelector(l.getAttribute('href'))).filter(Boolean);
    if (bar || links.length) {
        const onScroll = () => {
            const h = document.documentElement;
            if (bar) bar.style.transform = `scaleX(${h.scrollTop / Math.max(1, h.scrollHeight - h.clientHeight)})`;
            let cur = null;
            secs.forEach((s) => { if (s.getBoundingClientRect().top < 120) cur = s.id; });
            links.forEach((l) => l.classList.toggle('is-active', l.getAttribute('href') === '#' + cur));
        };
        addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }
});
