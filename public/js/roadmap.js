document.addEventListener('DOMContentLoaded', () => {
  const dialog = document.querySelector('.roadmap-dialog');
  const trigger = document.querySelector('.roadmap-open');
  if (!dialog || !trigger) return;

  const viewer = dialog.querySelector('.roadmap-viewer');
  const image = viewer.querySelector('img');
  const zoomIn = dialog.querySelector('[data-roadmap-in]');
  const zoomOut = dialog.querySelector('[data-roadmap-out]');
  const zoomLabel = dialog.querySelector('.roadmap-zoom-label');
  let zoom = 1;
  let drag = null;

  const renderZoom = (reset = false) => {
    if (!image.naturalWidth || !dialog.open) return;
    const fitWidth = Math.min(viewer.clientWidth, viewer.clientHeight * image.naturalWidth / image.naturalHeight, image.naturalWidth);
    image.style.width = `${Math.round(fitWidth * zoom)}px`;
    zoomLabel.textContent = `${Math.round(zoom * 100)}%`;
    zoomOut.disabled = zoom <= 1;
    zoomIn.disabled = zoom >= 4;
    viewer.classList.toggle('is-zoomed', zoom > 1);
    if (reset) {
      viewer.scrollTop = 0;
      viewer.scrollLeft = 0;
    }
  };

  trigger.addEventListener('click', () => {
    zoom = 1;
    dialog.showModal();
    document.body.classList.add('modal-open');
    renderZoom(true);
  });
  image.addEventListener('load', () => renderZoom(true));
  zoomIn.addEventListener('click', () => { zoom = Math.min(4, zoom + .5); renderZoom(); });
  zoomOut.addEventListener('click', () => { zoom = Math.max(1, zoom - .5); renderZoom(); });
  dialog.querySelector('[data-roadmap-fit]').addEventListener('click', () => { zoom = 1; renderZoom(true); });
  dialog.querySelector('.roadmap-close').addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
  dialog.addEventListener('close', () => {
    document.body.classList.remove('modal-open');
    drag = null;
    viewer.classList.remove('is-dragging');
    trigger.focus({ preventScroll: true });
  });
  viewer.addEventListener('pointerdown', event => {
    if (event.pointerType !== 'mouse' || event.button !== 0 || zoom <= 1) return;
    event.preventDefault();
    viewer.focus({ preventScroll: true });
    drag = { x: event.clientX, y: event.clientY, left: viewer.scrollLeft, top: viewer.scrollTop };
    viewer.setPointerCapture(event.pointerId);
    viewer.classList.add('is-dragging');
  });
  viewer.addEventListener('pointermove', event => {
    if (!drag) return;
    viewer.scrollLeft = drag.left - (event.clientX - drag.x);
    viewer.scrollTop = drag.top - (event.clientY - drag.y);
  });
  const stopDrag = () => { drag = null; viewer.classList.remove('is-dragging'); };
  viewer.addEventListener('pointerup', stopDrag);
  viewer.addEventListener('pointercancel', stopDrag);
  viewer.addEventListener('lostpointercapture', stopDrag);
  window.addEventListener('resize', () => renderZoom());
});
