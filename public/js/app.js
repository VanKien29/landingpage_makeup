/* ============================================================
   APP.JS — VAN CUONG MAKEUP & ACADEMY
   Main Application Logic
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
  // Respect reduced motion
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ===== Lenis Smooth Scroll =====
  let lenis = null;
  if (!prefersReducedMotion) {
    try {
      lenis = new Lenis({
        duration: 1.2,
        easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
        orientation: 'vertical',
        smoothWheel: true,
        touchMultiplier: 1.5,
      });

      function raf(time) {
        lenis.raf(time);
        requestAnimationFrame(raf);
      }
      requestAnimationFrame(raf);

      // Sync with GSAP ScrollTrigger
      if (typeof ScrollTrigger !== 'undefined') {
        lenis.on('scroll', ScrollTrigger.update);
        gsap.ticker.add((time) => {
          lenis.raf(time * 1000);
        });
        gsap.ticker.lagSmoothing(0);
      }
    } catch(e) {
      console.log('Lenis not available, using native scroll');
    }
  }

  // ===== Header Scroll =====
  const header = document.getElementById('site-header');
  let lastScroll = 0;

  window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    if (currentScroll > 80) {
      header.classList.add('is-scrolled');
    } else {
      header.classList.remove('is-scrolled');
    }
    lastScroll = currentScroll;
  }, { passive: true });

  // ===== Mobile Menu =====
  const menuToggle = document.getElementById('menu-toggle');
  const mobileNav = document.getElementById('mobile-nav');
  const mobileNavLinks = document.querySelectorAll('[data-nav]');

  if (menuToggle && mobileNav) {
    menuToggle.addEventListener('click', () => {
      const isOpen = mobileNav.classList.contains('is-open');
      menuToggle.classList.toggle('is-active');
      mobileNav.classList.toggle('is-open');
      menuToggle.setAttribute('aria-expanded', !isOpen);
      document.body.style.overflow = isOpen ? '' : 'hidden';
      if (lenis) {
        isOpen ? lenis.start() : lenis.stop();
      }
    });

    mobileNavLinks.forEach(link => {
      link.addEventListener('click', () => {
        menuToggle.classList.remove('is-active');
        mobileNav.classList.remove('is-open');
        menuToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        if (lenis) lenis.start();
      });
    });
  }

  // ===== Smooth anchor scroll =====
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', (e) => {
      const targetId = anchor.getAttribute('href');
      if (targetId === '#') return;
      const target = document.querySelector(targetId);
      if (target) {
        e.preventDefault();
        const headerHeight = header ? header.offsetHeight : 0;
        const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight - 20;
        
        if (lenis) {
          lenis.scrollTo(target, { offset: -(headerHeight + 20) });
        } else {
          window.scrollTo({ top: targetPosition, behavior: 'smooth' });
        }
      }
    });
  });

  // ===== Accordion =====
  const accordionTriggers = document.querySelectorAll('.accordion-trigger');
  accordionTriggers.forEach(trigger => {
    trigger.addEventListener('click', () => {
      const isExpanded = trigger.getAttribute('aria-expanded') === 'true';
      const content = trigger.nextElementSibling;

      // Close all others in same parent
      const parent = trigger.closest('.curriculum-accordion');
      if (parent) {
        parent.querySelectorAll('.accordion-trigger').forEach(otherTrigger => {
          if (otherTrigger !== trigger) {
            otherTrigger.setAttribute('aria-expanded', 'false');
            const otherContent = otherTrigger.nextElementSibling;
            if (otherContent) otherContent.style.maxHeight = null;
          }
        });
      }

      trigger.setAttribute('aria-expanded', !isExpanded);
      if (!isExpanded) {
        content.style.maxHeight = content.scrollHeight + 'px';
      } else {
        content.style.maxHeight = null;
      }
    });
  });

  // ===== Form Validation =====
  const form = document.getElementById('enrollment-form');
  const formSuccess = document.getElementById('form-success');

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      let isValid = true;

      // Validate required fields
      form.querySelectorAll('[required]').forEach(field => {
        const group = field.closest('.form-group') || field.closest('.form-checkbox')?.parentElement;
        if (!group) return;

        if (field.type === 'checkbox' && !field.checked) {
          group.classList.add('has-error');
          isValid = false;
        } else if (field.type !== 'checkbox' && !field.value.trim()) {
          group.classList.add('has-error');
          isValid = false;
        } else {
          group.classList.remove('has-error');
        }
      });

      if (!isValid) return;

      const submitBtn = document.getElementById('submit-btn');
      submitBtn.textContent = 'Dang gui...';
      submitBtn.disabled = true;

      try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
          }
        });

        if (response.ok) {
          form.style.display = 'none';
          formSuccess.classList.add('is-visible');
        } else {
          const data = await response.json();
          alert(data.message || 'Co loi xay ra. Vui long thu lai.');
          submitBtn.textContent = 'Gui Dang Ky Tu Van';
          submitBtn.disabled = false;
        }
      } catch (error) {
        alert('Khong the ket noi. Vui long thu lai sau.');
        submitBtn.textContent = 'Gui Dang Ky Tu Van';
        submitBtn.disabled = false;
      }
    });

    // Remove error on input
    form.querySelectorAll('.form-input, .form-textarea').forEach(input => {
      input.addEventListener('input', () => {
        input.closest('.form-group')?.classList.remove('has-error');
      });
    });

    form.querySelectorAll('input[type="checkbox"]').forEach(cb => {
      cb.addEventListener('change', () => {
        cb.closest('.form-group')?.classList.remove('has-error');
      });
    });
  }

  // ===== Mobile Fixed CTA =====
  const mobileCta = document.getElementById('mobile-cta');
  if (mobileCta) {
    const heroSection = document.querySelector('.hero');
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          mobileCta.classList.remove('is-visible');
        } else {
          mobileCta.classList.add('is-visible');
        }
      });
    }, { threshold: 0.3 });

    if (heroSection) observer.observe(heroSection);
  }
});
