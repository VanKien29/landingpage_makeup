/* ============================================================
   ANIMATIONS.JS — VAN CUONG MAKEUP & ACADEMY
   GSAP & ScrollTrigger Luxury Motion Design
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (prefersReducedMotion) return;

  // Ensure GSAP is available
  if (typeof gsap === 'undefined') return;
  if (typeof ScrollTrigger !== 'undefined') {
    gsap.registerPlugin(ScrollTrigger);
  }

  // ===== 1. Hero Entrance Animations =====
  const heroTl = gsap.timeline({ defaults: { ease: 'power3.out' } });

  heroTl
    .fromTo('.hero-bg img', 
      { scale: 1.15, filter: 'blur(8px)' }, 
      { scale: 1, filter: 'blur(0px)', duration: 2.2, ease: 'power2.out' }
    )
    .fromTo('.hero-eyebrow', 
      { y: 30, opacity: 0 }, 
      { y: 0, opacity: 1, duration: 1 }, 
      '-=1.6'
    )
    .fromTo('.hero-title', 
      { y: 40, opacity: 0 }, 
      { y: 0, opacity: 1, duration: 1.2 }, 
      '-=0.8'
    )
    .fromTo('.hero-subtitle', 
      { y: 25, opacity: 0 }, 
      { y: 0, opacity: 1, duration: 1 }, 
      '-=0.8'
    )
    .fromTo('.hero-stats', 
      { y: 20, opacity: 0 }, 
      { y: 0, opacity: 1, duration: 0.8 }, 
      '-=0.6'
    )
    .fromTo('.hero-cta-group', 
      { y: 20, opacity: 0 }, 
      { y: 0, opacity: 1, duration: 0.8 }, 
      '-=0.6'
    );

  // ===== 2. Hero Background Parallax =====
  if (typeof ScrollTrigger !== 'undefined') {
    gsap.to('.hero-bg img', {
      scrollTrigger: {
        trigger: '.hero',
        start: 'top top',
        end: 'bottom top',
        scrub: true,
      },
      y: 100,
      scale: 1.05,
      ease: 'none',
    });
  }

  // ===== 3. Scroll Reveal Elements =====
  if (typeof ScrollTrigger !== 'undefined') {
    const revealElements = document.querySelectorAll('.reveal:not(.hero-content *)');
    revealElements.forEach((el) => {
      gsap.fromTo(el, 
        { y: 40, opacity: 0 }, 
        {
          scrollTrigger: {
            trigger: el,
            start: 'top 88%',
            toggleActions: 'play none none none',
          },
          y: 0,
          opacity: 1,
          duration: 0.9,
          ease: 'power2.out',
        }
      );
    });

    // Stagger containers
    const staggerContainers = document.querySelectorAll('.stagger');
    staggerContainers.forEach((container) => {
      const children = container.children;
      gsap.fromTo(children,
        { y: 30, opacity: 0 },
        {
          scrollTrigger: {
            trigger: container,
            start: 'top 85%',
            toggleActions: 'play none none none',
          },
          y: 0,
          opacity: 1,
          duration: 0.7,
          stagger: 0.15,
          ease: 'power2.out',
        }
      );
    });

    // Reveal Scale for cards & portfolio items
    const scaleElements = document.querySelectorAll('.reveal-scale');
    scaleElements.forEach((el, index) => {
      gsap.fromTo(el,
        { scale: 0.94, opacity: 0, y: 20 },
        {
          scrollTrigger: {
            trigger: el,
            start: 'top 90%',
            toggleActions: 'play none none none',
          },
          scale: 1,
          opacity: 1,
          y: 0,
          duration: 0.8,
          delay: (index % 3) * 0.1,
          ease: 'power2.out',
        }
      );
    });
  }

  // ===== 4. Number Counters Animation =====
  const statNumbers = document.querySelectorAll('.hero-stat-number');
  statNumbers.forEach((stat) => {
    const text = stat.textContent.trim();
    if (text === '40') {
      animateCounter(stat, 0, 40, 1800);
    }
  });

  function animateCounter(el, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
      if (!startTimestamp) startTimestamp = timestamp;
      const progress = Math.min((timestamp - startTimestamp) / duration, 1);
      const easeProgress = 1 - Math.pow(1 - progress, 3);
      const current = Math.floor(easeProgress * (end - start) + start);
      el.textContent = current < 10 ? '0' + current : current;
      if (progress < 1) {
        window.requestAnimationFrame(step);
      } else {
        el.textContent = end < 10 ? '0' + end : end;
      }
    };
    window.requestAnimationFrame(step);
  }
});