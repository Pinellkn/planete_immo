// ============================================================
// BÉNIN IMMO - JavaScript Principal
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    // ---- Navbar scroll effect ----
    const navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 60) navbar.classList.add('scrolled');
            else navbar.classList.remove('scrolled');
        });
    }

    // ---- Back to top ----
    const backBtn = document.getElementById('backToTop');
    if (backBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 400) backBtn.classList.add('show');
            else backBtn.classList.remove('show');
        });
        backBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ---- Flash message auto-close ----
    const flash = document.getElementById('flashMsg');
    if (flash) setTimeout(() => flash.style.opacity = '0', 4000);

    // ---- Password toggle ----
    document.querySelectorAll('.password-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.previousElementSibling || btn.parentElement.querySelector('input');
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    input.type = 'password';
                    btn.innerHTML = '<i class="fas fa-eye"></i>';
                }
            }
        });
    });

    // ---- Animation au scroll ----
    const animEls = document.querySelectorAll('.animate-on-scroll');
    if (animEls.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, { threshold: 0.1 });
        animEls.forEach(el => observer.observe(el));
    }

    // ---- Counter animation ----
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                counter.textContent = target.toLocaleString('fr-FR');
                clearInterval(timer);
            } else {
                counter.textContent = Math.floor(current).toLocaleString('fr-FR');
            }
        }, 16);
    });

    // ---- Favoris toggle ----
    document.querySelectorAll('.toggle-fav').forEach(btn => {
        btn.addEventListener('click', async function (e) {
            e.preventDefault();
            const maisonId = this.getAttribute('data-id');
            if (!maisonId) return;
            try {
                const res = await fetch(SITE_URL + '/ajax/toggle_favori.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `maison_id=${maisonId}`
                });
                const data = await res.json();
                if (data.status === 'added') {
                    this.classList.add('active');
                    this.innerHTML = '<i class="fas fa-heart"></i>';
                } else if (data.status === 'removed') {
                    this.classList.remove('active');
                    this.innerHTML = '<i class="far fa-heart"></i>';
                } else if (data.status === 'login') {
                    window.location.href = SITE_URL + '/login.php';
                }
            } catch (err) { console.error(err); }
        });
    });

    // ---- Galerie photos ----
    const mainImg = document.getElementById('mainPhoto');
    document.querySelectorAll('.gallery-thumb').forEach(thumb => {
        thumb.addEventListener('click', function () {
            document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            if (mainImg) {
                mainImg.style.opacity = '0';
                setTimeout(() => {
                    mainImg.src = this.getAttribute('data-src');
                    mainImg.style.opacity = '1';
                }, 200);
            }
        });
    });

    // ---- Sidebar mobile toggle ----
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
    }

    // ---- Role selector register ----
    document.querySelectorAll('.role-option').forEach(opt => {
        opt.addEventListener('click', function () {
            document.querySelectorAll('.role-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });

    // ---- Dropdown tables filter ----
    const tableSearch = document.getElementById('tableSearch');
    if (tableSearch) {
        tableSearch.addEventListener('input', function () {
            const val = this.value.toLowerCase();
            document.querySelectorAll('.data-table tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
            });
        });
    }

});

// ---- Nav toggle mobile ----
function toggleNav() {
    const links = document.getElementById('navLinks');
    if (links) links.classList.toggle('open');
}

// ---- Confirm delete ----
function confirmDelete(url, msg) {
    if (confirm(msg || 'Êtes-vous sûr de vouloir supprimer cet élément ?')) {
        window.location.href = url;
    }
}

// SITE_URL global (défini dans chaque page)
if (typeof SITE_URL === 'undefined') var SITE_URL = '';
