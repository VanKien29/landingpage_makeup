document.addEventListener('DOMContentLoaded', () => {
  const dialog = document.querySelector('.video-lightbox');
  if (!dialog) return;

  const video = dialog.querySelector('video');
  const title = dialog.querySelector('#video-lightbox-title');
  const error = dialog.querySelector('.video-error');
  let activeButton;

  document.querySelectorAll('.film-open').forEach(button => {
    button.addEventListener('click', () => {
      activeButton = button;
      title.textContent = button.dataset.videoTitle;
      dialog.dataset.orientation = button.dataset.orientation;
      error.hidden = true;
      error.querySelector('a').href = button.dataset.videoSrc;
      video.poster = button.dataset.videoPoster;
      video.src = button.dataset.videoSrc;
      video.setAttribute('aria-label', button.dataset.videoTitle);
      dialog.showModal();
      document.body.classList.add('modal-open');
      video.play().catch(() => {});
    });
  });

  video.addEventListener('error', () => { error.hidden = false; });
  dialog.querySelector('.video-close').addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', event => {
    if (event.target === dialog) dialog.close();
  });
  dialog.addEventListener('close', () => {
    video.pause();
    video.removeAttribute('src');
    video.load();
    document.body.classList.remove('modal-open');
    activeButton?.focus({ preventScroll: true });
  });
});
