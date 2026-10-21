document.addEventListener('DOMContentLoaded', () => {
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const banner = document.querySelector('.hero-banner');
  const slides = [...document.querySelectorAll('.banner-slide')];
  let slideIndex = 0;
  let bannerPaused = reducedMotion.matches;
  let bannerTimer;
  let slideRequest = 0;

  const loadSlide = async slide => {
    const mobile = window.matchMedia('(max-width: 700px)').matches;
    await Promise.all([...slide.querySelectorAll('img')].map(async image => {
      if (mobile && image.classList.contains('banner-side')) return;
      if (image.dataset.src) {
        if (image.dataset.srcset) {
          image.srcset = image.dataset.srcset;
          delete image.dataset.srcset;
        }
        image.src = image.dataset.src;
        delete image.dataset.src;
      }
      await image.decode?.().catch(() => {});
    }));
  };

  const showSlide = async index => {
    if (!slides.length) return;
    const request = ++slideRequest;
    const nextIndex = (index + slides.length) % slides.length;
    await loadSlide(slides[nextIndex]);
    if (request !== slideRequest) return;
    slideIndex = nextIndex;
    slides.forEach((slide, position) => slide.classList.toggle('is-active', position === slideIndex));
    const counter = document.querySelector('.banner-counter');
    if (counter) counter.textContent = `${String(slideIndex + 1).padStart(2, '0')} / ${String(slides.length).padStart(2, '0')}`;
  };
  const updatePlayback = () => {
    clearInterval(bannerTimer);
    if (slides.length > 1 && !bannerPaused && !document.hidden) {
      bannerTimer = setInterval(() => {
        if (banner && banner.getBoundingClientRect().bottom > 0) showSlide(slideIndex + 1);
      }, 7000);
    }
  };
  document.querySelector('.banner-prev')?.addEventListener('click', () => { showSlide(slideIndex - 1); updatePlayback(); });
  document.querySelector('.banner-next')?.addEventListener('click', () => { showSlide(slideIndex + 1); updatePlayback(); });
  document.addEventListener('visibilitychange', updatePlayback);
  if (slides[0]?.classList.contains('is-initial')) {
    requestAnimationFrame(() => requestAnimationFrame(() => slides[0].classList.remove('is-initial')));
  }
  const startBanner = () => {
    if (slides[0]) loadSlide(slides[0]);
    updatePlayback();
  };
  if (document.readyState === 'complete') startBanner();
  else window.addEventListener('load', startBanner, { once: true });
  window.matchMedia('(min-width: 701px)').addEventListener('change', () => {
    if (slides[slideIndex]) loadSlide(slides[slideIndex]);
  });

  const scrollTexts = [...document.querySelectorAll('[data-scroll-text]')];
  scrollTexts.forEach(track => {
    const copy = document.createElement('div');
    copy.className = 'scroll-type-copy';
    while (track.firstChild) copy.append(track.firstChild);
    track.append(copy, copy.cloneNode(true));
    track.setAttribute('aria-hidden', 'true');

  });

  const fillMovingTrack = (track, width, minimumWidth) => {
    if (width <= 0) return;
    const repetitions = Math.min(8, Math.ceil(minimumWidth / width) - 1);
    const fragment = document.createDocumentFragment();
    for (let index = 0; index < repetitions; index++) {
      [...track.children].forEach(child => fragment.append(child.cloneNode(true)));
    }
    track.append(fragment);
  };
  const fillMovingTracks = () => {
    const minimumWidth = window.innerWidth * 2;
    const measurements = [...document.querySelectorAll('.marquee-track, [data-scroll-text]')].map(track => ({track, width: track.scrollWidth}));
    measurements.forEach(({track, width}) => fillMovingTrack(track, width, minimumWidth));
  };
  requestAnimationFrame(fillMovingTracks);
  window.addEventListener('load', fillMovingTracks, { once: true });
  window.addEventListener('resize', fillMovingTracks, { passive: true });
  const parallaxFrames = [...document.querySelectorAll('.educator-visual, .course-image')];
  const progress = document.querySelector('.reading-progress');

  let framePending = false;
  const clamp = (value, minimum, maximum) => Math.max(minimum, Math.min(maximum, value));
  const drawScrollFrame = () => {
    framePending = false;
    const viewportHeight = innerHeight;
    const scrollableHeight = document.documentElement.scrollHeight - viewportHeight;
    const frames = reducedMotion.matches ? [] : parallaxFrames.map(element => ({ element, bounds: element.getBoundingClientRect() }));
    if (progress) progress.style.transform = `scaleX(${scrollableHeight > 0 ? scrollY / scrollableHeight : 0})`;

    if (!reducedMotion.matches) {
      frames.forEach(({ element, bounds }) => {
        if (bounds.bottom >= 0 && bounds.top <= viewportHeight) {
          const amount = clamp((bounds.top + bounds.height / 2 - viewportHeight / 2) / viewportHeight, -1, 1);
          element.style.setProperty('--parallax-y', `${amount * 20}px`);
        }
      });
    }

  };
  const requestScrollFrame = () => {
    if (!framePending) {
      framePending = true;
      requestAnimationFrame(drawScrollFrame);
    }
  };

  if ('IntersectionObserver' in window && !reducedMotion.matches) {
    const observer = new IntersectionObserver(entries => entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-revealed');
        observer.unobserve(entry.target);
      }
    }), { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
    document.querySelectorAll('.section-top, .section-heading, .educator-copy, .educator-visual, .educator-supporting figure, .curriculum-grid > div, .schedule-grid article, .portfolio-heading, .commitment-intro, .commitment-grid article, .tuition-intro, .tuition-detail, .enrollment-grid > div, .manifesto').forEach((element, index) => {
      element.classList.add('scroll-reveal');
      element.style.setProperty('--reveal-delay', `${index % 3 * 70}ms`);
      observer.observe(element);
    });
    parallaxFrames.forEach(element => element.classList.add('parallax-frame'));
    document.documentElement.classList.add('motion-ready');
  }

  const handleLayoutChange = () => { requestScrollFrame(); };
  window.addEventListener('scroll', requestScrollFrame, { passive: true });
  window.addEventListener('resize', handleLayoutChange, { passive: true });
  window.addEventListener('load', handleLayoutChange, { once: true });
  document.fonts?.ready.then(handleLayoutChange);
  reducedMotion.addEventListener('change', () => {
    bannerPaused = reducedMotion.matches;
    if (reducedMotion.matches) {
      document.querySelectorAll('.scroll-reveal').forEach(element => element.classList.add('is-revealed'));
      scrollTexts.forEach(text => { text.style.transform = ''; });
      parallaxFrames.forEach(element => element.style.setProperty('--parallax-y', '0px'));
    }
    updatePlayback();
    handleLayoutChange();
  });

  requestScrollFrame();
});

