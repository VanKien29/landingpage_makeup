document.addEventListener('DOMContentLoaded', () => {
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const hoverAvailable = window.matchMedia('(hover: hover) and (pointer: fine)');

  document.querySelectorAll('[data-journey-carousel]').forEach(carousel => {
    const viewport = carousel.querySelector('.journey-window');
    const track = carousel.querySelector('.journey-grid');
    const progress = carousel.querySelector('.rail-progress > span');
    const originals = [...track.children];
    let cycleWidth = 0;
    let frame = null;
    let previousTime = null;
    let visible = false;
    let hovered = false;
    let focused = false;
    let position = 0;
    let resumeAt = 0;
    let resumeTimer;

    originals.forEach(figure => {
      const copy = figure.cloneNode(true);
      copy.setAttribute('aria-hidden', 'true');
      copy.setAttribute('inert', '');
      copy.dataset.journeyCopy = '';
      track.append(copy);
    });
    viewport.setAttribute('tabindex', '0');
    viewport.setAttribute('role', 'region');
    viewport.setAttribute('aria-label', 'Hành trình học tự động — có thể vuốt ngang hoặc sử dụng các nút chuyển chặng');

    const updateProgress = () => {
      if (progress && cycleWidth) progress.style.transform = `translateX(${((viewport.scrollLeft % cycleWidth) / cycleWidth) * 400}%)`;
    };
    const stop = () => {
      if (frame !== null) cancelAnimationFrame(frame);
      frame = null;
      previousTime = null;
    };
    const canPlay = () => visible && !document.hidden && !reducedMotion.matches && !hovered && !focused && cycleWidth > 0 && performance.now() >= resumeAt;
    const tick = time => {
      frame = null;
      if (!canPlay()) { previousTime = null; return; }
      const elapsed = previousTime === null ? 0 : Math.min(time - previousTime, 50);
      previousTime = time;
      position = (position + elapsed * .024) % cycleWidth;
      viewport.scrollLeft = position;
      updateProgress();
      frame = requestAnimationFrame(tick);
    };
    const play = () => {
      if (!canPlay()) { stop(); return; }
      if (frame !== null) return;
      position = viewport.scrollLeft % cycleWidth;
      viewport.scrollLeft = position;
      previousTime = null;
      frame = requestAnimationFrame(tick);
    };
    const temporarilyPause = () => {
      stop();
      resumeAt = performance.now() + 6000;
      clearTimeout(resumeTimer);
      resumeTimer = setTimeout(play, 6050);
    };
    const updateLayout = () => {
      stop();
      const firstCopy = track.querySelector('[data-journey-copy]');
      cycleWidth = firstCopy.offsetLeft - originals[0].offsetLeft;
      if (cycleWidth > 0) viewport.scrollLeft %= cycleWidth;
      updateProgress();
      play();
    };
    const move = direction => {
      temporarilyPause();
      const cardStep = originals.length > 1 ? originals[1].offsetLeft - originals[0].offsetLeft : viewport.clientWidth;
      if (direction < 0 && viewport.scrollLeft < cardStep) viewport.scrollLeft += cycleWidth;
      if (direction > 0 && viewport.scrollLeft >= cycleWidth) viewport.scrollLeft -= cycleWidth;
      viewport.scrollBy({ left: cardStep * direction, behavior: reducedMotion.matches ? 'instant' : 'smooth' });
    };

    carousel.querySelector('[data-rail-prev]').addEventListener('click', () => move(-1));
    carousel.querySelector('[data-rail-next]').addEventListener('click', () => move(1));
    viewport.addEventListener('pointerenter', () => { if (hoverAvailable.matches) { hovered = true; stop(); } });
    viewport.addEventListener('pointerleave', () => { hovered = false; resumeAt = 0; play(); });
    viewport.addEventListener('pointerdown', temporarilyPause, { passive: true });
    viewport.addEventListener('wheel', temporarilyPause, { passive: true });
    viewport.addEventListener('focusin', () => { focused = true; stop(); });
    viewport.addEventListener('focusout', () => { focused = false; play(); });
    viewport.addEventListener('scroll', updateProgress, { passive: true });
    viewport.addEventListener('keydown', event => {
      if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
        event.preventDefault();
        move(event.key === 'ArrowRight' ? 1 : -1);
      }
    });
    const observer = new IntersectionObserver(entries => {
      visible = entries[0].isIntersecting;
      play();
    }, { threshold: .1 });
    observer.observe(viewport);
    document.addEventListener('visibilitychange', play);
    reducedMotion.addEventListener('change', play);
    window.addEventListener('resize', updateLayout, { passive: true });
    window.addEventListener('load', updateLayout, { once: true });
    document.fonts?.ready.then(updateLayout);
    updateLayout();
  });
});

