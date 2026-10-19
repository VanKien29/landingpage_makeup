document.addEventListener('DOMContentLoaded', () => {
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  let frame = null;
  let target = window.scrollY;
  let previousTime = null;

  const stop = () => {
    if (frame !== null) cancelAnimationFrame(frame);
    frame = null;
    previousTime = null;
    target = window.scrollY;
    document.documentElement.classList.remove('wheel-scrolling');
  };
  const maximumScroll = () => Math.max(0, document.documentElement.scrollHeight - innerHeight);
  const clamp = value => Math.max(0, Math.min(maximumScroll(), value));
  const animate = time => {
    const elapsed = previousTime === null ? 16 : Math.min(time - previousTime, 40);
    previousTime = time;
    target = clamp(target);
    const remaining = target - window.scrollY;

    if (Math.abs(remaining) < 4) {
      window.scrollTo({ top: target, behavior: 'instant' });
      stop();
      return;
    }

    window.scrollTo({ top: window.scrollY + remaining * (1 - Math.exp(-elapsed / 125)), behavior: 'instant' });
    frame = requestAnimationFrame(animate);
  };
  const nestedScrollerCanConsume = (element, delta) => {
    for (let current = element; current && current !== document.body; current = current.parentElement) {
      if (current.scrollHeight <= current.clientHeight + 1) continue;
      if (!/(auto|scroll)/.test(getComputedStyle(current).overflowY)) continue;
      if ((delta < 0 && current.scrollTop > 0) || (delta > 0 && current.scrollTop + current.clientHeight < current.scrollHeight - 1)) return true;
    }
    return false;
  };

  window.addEventListener('wheel', event => {
    const element = event.target instanceof Element ? event.target : null;
    if (reducedMotion.matches || event.ctrlKey || event.metaKey || event.shiftKey || !event.cancelable || Math.abs(event.deltaX) > Math.abs(event.deltaY) || document.body.classList.contains('modal-open') || element?.closest('dialog[open], textarea, select, input, [contenteditable="true"], [data-native-scroll]')) {
      stop();
      return;
    }

    // Small pixel deltas already carry the trackpad's native momentum.
    if (event.deltaMode === 0 && Math.abs(event.deltaY) < 45) {
      stop();
      return;
    }

    const unit = event.deltaMode === 1 ? 18 : event.deltaMode === 2 ? innerHeight : 1;
    const delta = Math.max(-innerHeight * .9, Math.min(innerHeight * .9, event.deltaY * unit)) * .92;
    if (!delta || nestedScrollerCanConsume(element, delta)) {
      stop();
      return;
    }

    const current = window.scrollY;
    if (frame === null || Math.sign(delta) !== Math.sign(target - current)) target = current;
    target = clamp(target + delta);
    if (target === current) return;

    event.preventDefault();
    document.documentElement.classList.add('wheel-scrolling');
    if (frame === null) frame = requestAnimationFrame(animate);
  }, { passive: false });

  document.addEventListener('pointerdown', stop, { passive: true, capture: true });
  document.addEventListener('touchstart', stop, { passive: true });
  document.addEventListener('click', event => { if (event.target.closest?.('a[href]')) stop(); }, true);
  document.addEventListener('keydown', event => { if (['ArrowUp', 'ArrowDown', 'PageUp', 'PageDown', 'Home', 'End', ' '].includes(event.key)) stop(); });
  document.addEventListener('visibilitychange', stop);
  window.addEventListener('hashchange', stop);
  window.addEventListener('popstate', stop);
  reducedMotion.addEventListener('change', stop);
});
