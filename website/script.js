/* =============================================
   YOUR COMPANY NAME — Enterprise SaaS Website
   script.js
   ============================================= */

'use strict';

/* ── Page Loader ─────────────────────────────── */
window.addEventListener('load', () => {
  const loader = document.getElementById('page-loader');
  if (!loader) return;
  setTimeout(() => {
    loader.classList.add('hidden');
    setTimeout(() => loader.remove(), 500);
  }, 1200);
});

document.addEventListener('DOMContentLoaded', () => {

  /* ── AOS Init ──────────────────────────────── */
  if (typeof AOS !== 'undefined') {
    AOS.init({
      duration: 680,
      easing: 'ease-out-cubic',
      once: true,
      offset: 70,
      delay: 50,
    });
  }

  /* ── Sticky Navbar ─────────────────────────── */
  const navbar = document.querySelector('.navbar-custom');
  if (navbar) {
    const onScroll = () => {
      navbar.classList.toggle('scrolled', window.scrollY > 50);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* ── Back to Top ───────────────────────────── */
  const btt = document.getElementById('backToTop');
  if (btt) {
    window.addEventListener('scroll', () => {
      btt.classList.toggle('visible', window.scrollY > 420);
    }, { passive: true });
    btt.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  /* ── Smooth Anchor Scrolling ───────────────── */
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const target = document.querySelector(a.getAttribute('href'));
      if (!target) return;
      e.preventDefault();
      const offset = (navbar ? navbar.offsetHeight : 80) + 20;
      window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - offset, behavior: 'smooth' });
    });
  });

  /* ── Active Nav Link ───────────────────────── */
  const page = location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
    const href = link.getAttribute('href');
    if (href === page || (page === '' && href === 'index.html')) {
      link.classList.add('active');
    }
  });

  /* ── Dropdown hover on desktop ─────────────── */
  if (window.innerWidth > 991) {
    document.querySelectorAll('.navbar-nav .dropdown').forEach(dd => {
      dd.addEventListener('mouseenter', () => {
        dd.querySelector('.dropdown-menu')?.classList.add('show');
        dd.querySelector('[data-bs-toggle]')?.setAttribute('aria-expanded', 'true');
      });
      dd.addEventListener('mouseleave', () => {
        dd.querySelector('.dropdown-menu')?.classList.remove('show');
        dd.querySelector('[data-bs-toggle]')?.setAttribute('aria-expanded', 'false');
      });
    });
  }

  /* ── Counter Animation ─────────────────────── */
  const counters = document.querySelectorAll('[data-counter]');
  if (counters.length) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        animateCounter(entry.target);
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.6 });
    counters.forEach(el => observer.observe(el));
  }

  function animateCounter(el) {
    const target   = parseFloat(el.dataset.counter);
    const suffix   = el.dataset.suffix || '';
    const prefix   = el.dataset.prefix || '';
    const decimals = el.dataset.decimals ? parseInt(el.dataset.decimals) : 0;
    const duration = 2200;
    const start    = performance.now();

    const tick = now => {
      const elapsed  = now - start;
      const progress = Math.min(elapsed / duration, 1);
      const eased    = 1 - Math.pow(1 - progress, 3); // ease-out-cubic
      const value    = target * eased;
      el.textContent = prefix + value.toFixed(decimals) + suffix;
      if (progress < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  }

  /* ── Form Handling ─────────────────────────── */
  document.querySelectorAll('form[data-form]').forEach(form => {
    form.addEventListener('submit', e => {
      e.preventDefault();
      if (!validateForm(form)) return;

      const btn         = form.querySelector('[type="submit"]');
      const origHTML    = btn.innerHTML;
      const alertBox    = form.querySelector('.form-alert');

      btn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i>Submitting…';
      btn.disabled  = true;

      // Simulate async submission
      setTimeout(() => {
        btn.innerHTML = '<i class="fas fa-check me-2"></i>Sent Successfully!';
        btn.style.background = 'linear-gradient(135deg, #10b981, #059669)';

        if (alertBox) {
          alertBox.className = 'form-alert success';
          alertBox.textContent = '✓ Thank you! We\'ll get back to you within 24 hours.';
          alertBox.style.display = 'block';
        }
        form.reset();

        setTimeout(() => {
          btn.innerHTML        = origHTML;
          btn.style.background = '';
          btn.disabled         = false;
          if (alertBox) alertBox.style.display = 'none';
        }, 4000);
      }, 1800);
    });

    // Real-time field validation
    form.querySelectorAll('[required]').forEach(field => {
      field.addEventListener('blur', () => validateField(field));
      field.addEventListener('input', () => {
        if (field.style.borderColor === 'rgb(239, 68, 68)') validateField(field);
      });
    });
  });

  function validateForm(form) {
    let valid = true;
    form.querySelectorAll('[required]').forEach(f => {
      if (!validateField(f)) valid = false;
    });
    return valid;
  }

  function validateField(field) {
    const val   = field.value.trim();
    const empty = val === '';
    const email = field.type === 'email' && val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
    const phone = field.type === 'tel'   && val && !/^[\d\s\+\-\(\)]{7,15}$/.test(val);

    const invalid = empty || email || phone;
    field.style.borderColor = invalid ? '#ef4444' : '';
    field.style.boxShadow   = invalid ? '0 0 0 3px rgba(239,68,68,.1)' : '';
    return !invalid;
  }

  /* ── Pricing Toggle (Monthly / Annual) ──────── */
  const toggle = document.getElementById('billingToggle');
  if (toggle) {
    toggle.addEventListener('change', () => {
      const isAnnual = toggle.checked;
      document.querySelectorAll('[data-monthly]').forEach(el => {
        el.textContent = isAnnual ? el.dataset.annual : el.dataset.monthly;
      });
      document.querySelectorAll('.toggle-label-m, .toggle-label-a').forEach(el => {
        el.classList.toggle('fw-bold', false);
      });
      if (isAnnual) {
        document.querySelector('.toggle-label-a')?.classList.add('fw-bold');
      } else {
        document.querySelector('.toggle-label-m')?.classList.add('fw-bold');
      }
    });
  }

  /* ── Particles (Hero background) ───────────── */
  initParticles();

  /* ── Chart Bar Animation ────────────────────── */
  document.querySelectorAll('.chart-bar-item').forEach((bar, i) => {
    bar.style.height   = '0%';
    bar.style.transition = `height .8s ease ${i * 0.08}s`;
    const target = bar.dataset.h || (Math.random() * 65 + 25);
    setTimeout(() => { bar.style.height = target + '%'; }, 200);
  });

  /* ── Navbar collapse on mobile link click ───── */
  const navCollapse = document.getElementById('navbarNav');
  if (navCollapse) {
    document.querySelectorAll('.navbar-nav .nav-link:not(.dropdown-toggle)').forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth < 992) {
          const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
          if (bsCollapse) bsCollapse.hide();
        }
      });
    });
  }

});

/* ── Particle Canvas ────────────────────────────── */
function initParticles() {
  const canvas = document.getElementById('particleCanvas');
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  let W = canvas.offsetWidth, H = canvas.offsetHeight;
  canvas.width = W; canvas.height = H;

  const COUNT  = 55;
  const colors = ['#6366f1', '#8b5cf6', '#06b6d4'];

  const particles = Array.from({ length: COUNT }, () => ({
    x: Math.random() * W,
    y: Math.random() * H,
    r: Math.random() * 1.8 + 0.4,
    vx: (Math.random() - 0.5) * 0.45,
    vy: (Math.random() - 0.5) * 0.45,
    o: Math.random() * 0.45 + 0.08,
    c: colors[Math.floor(Math.random() * colors.length)],
  }));

  let raf;
  function draw() {
    ctx.clearRect(0, 0, W, H);
    particles.forEach(p => {
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = p.c;
      ctx.globalAlpha = p.o;
      ctx.fill();
      p.x += p.vx; p.y += p.vy;
      if (p.x < 0) p.x = W; if (p.x > W) p.x = 0;
      if (p.y < 0) p.y = H; if (p.y > H) p.y = 0;
    });
    ctx.globalAlpha = 1;
    raf = requestAnimationFrame(draw);
  }
  draw();

  window.addEventListener('resize', () => {
    W = canvas.offsetWidth; H = canvas.offsetHeight;
    canvas.width = W; canvas.height = H;
  });
}
