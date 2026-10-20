document.addEventListener('DOMContentLoaded', () => {
  const dialog = document.querySelector('.roadmap-dialog');
  const triggers = document.querySelectorAll('.roadmap-open');
  if (!dialog || !triggers.length) return;

  const content = dialog.querySelector('.roadmap-dialog-body');
  let activeTrigger = null;

  triggers.forEach(trigger => {
    trigger.addEventListener('click', () => {
      activeTrigger = trigger;
      dialog.showModal();
      document.body.classList.add('modal-open');
      content.scrollTop = 0;
    });
  });

  dialog.querySelectorAll('.roadmap-close, [data-roadmap-close]').forEach(button => {
    button.addEventListener('click', () => dialog.close());
  });

  let pointerStartedOutside = false;
  const isOutside = event => {
    const bounds = dialog.getBoundingClientRect();
    return event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom;
  };
  dialog.addEventListener('pointerdown', event => { pointerStartedOutside = isOutside(event); });
  dialog.addEventListener('click', event => {
    if (event.target === dialog && pointerStartedOutside && isOutside(event)) dialog.close();
    pointerStartedOutside = false;
  });

  dialog.querySelectorAll('[data-roadmap-month]').forEach(button => {
    button.addEventListener('click', () => {
      const month = document.getElementById(button.dataset.roadmapMonth);
      month.focus({ preventScroll: true });
      content.scrollTo({
        top: content.scrollTop + month.getBoundingClientRect().top - content.getBoundingClientRect().top - 24,
        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth',
      });
    });
  });

  dialog.addEventListener('close', () => {
    document.body.classList.remove('modal-open');
    activeTrigger?.focus({ preventScroll: true });
  });
});
