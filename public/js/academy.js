document.addEventListener('DOMContentLoaded', () => {
  const menu = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.main-nav');
  const closeMenu = () => { nav?.classList.remove('is-open'); menu?.setAttribute('aria-expanded', 'false'); menu?.setAttribute('aria-label', 'Mở menu'); };
  menu?.addEventListener('click', () => {
    const open = menu.getAttribute('aria-expanded') !== 'true';
    nav.classList.toggle('is-open', open);
    menu.setAttribute('aria-expanded', String(open));
    menu.setAttribute('aria-label', open ? 'Đóng menu' : 'Mở menu');
  });
  nav?.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
  document.addEventListener('keydown', event => { if (event.key === 'Escape') closeMenu(); });
  document.addEventListener('click', event => { if (!event.target.closest('.site-header')) closeMenu(); });

  document.querySelectorAll('.accordion details').forEach(detail => {
    detail.addEventListener('toggle', () => {
      if (detail.open) document.querySelectorAll('.accordion details').forEach(other => { if (other !== detail) other.open = false; });
    });
  });

  const filters = [...document.querySelectorAll('[data-filter]')];
  const items = [...document.querySelectorAll('.gallery-item')];
  const moreButton = document.querySelector('[data-gallery-more]');
  const galleryResult = document.querySelector('.gallery-result');
  let currentFilter = 'all';
  let visibleLimit = 9;
  const renderGallery = () => {
    const matching = items.filter(item => currentFilter === 'all' || item.dataset.category === currentFilter);
    items.forEach(item => { item.hidden = !matching.includes(item) || (currentFilter === 'all' && matching.indexOf(item) >= visibleLimit); });
    if (moreButton) moreButton.hidden = currentFilter !== 'all' || matching.length <= visibleLimit;
    if (galleryResult) galleryResult.textContent = matching.length ? `${Math.min(currentFilter === 'all' ? visibleLimit : matching.length, matching.length)} / ${matching.length} tác phẩm` : 'Bộ sưu tập đang được cập nhật.';
  };
  filters.forEach(button => button.addEventListener('click', () => {
    filters.forEach(filter => filter.setAttribute('aria-pressed', String(filter === button)));
    currentFilter = button.dataset.filter;
    visibleLimit = 9;
    renderGallery();
  }));
  moreButton?.addEventListener('click', () => { visibleLimit += 9; renderGallery(); });
  renderGallery();

  const lightbox = document.querySelector('.lightbox');
  if (lightbox) {
    let currentIndex = 0;
    let visibleButtons = [];
    const showImage = index => {
      currentIndex = (index + visibleButtons.length) % visibleButtons.length;
      const button = visibleButtons[currentIndex];
      lightbox.querySelector('img').src = button.dataset.src;
      lightbox.querySelector('img').alt = button.querySelector('img').alt;
      lightbox.querySelector('figcaption').textContent = button.dataset.caption;
      lightbox.querySelector('.lightbox-count').textContent = `${currentIndex + 1} / ${visibleButtons.length}`;
    };
    document.querySelectorAll('.gallery-open').forEach(button => button.addEventListener('click', () => {
      visibleButtons = items.filter(item => !item.hidden).map(item => item.querySelector('.gallery-open'));
      showImage(visibleButtons.indexOf(button));
      lightbox.showModal();
      document.body.classList.add('modal-open');
    }));
    lightbox.querySelector('.lightbox-close').addEventListener('click', () => lightbox.close());
    lightbox.querySelector('.lightbox-prev').addEventListener('click', () => showImage(currentIndex - 1));
    lightbox.querySelector('.lightbox-next').addEventListener('click', () => showImage(currentIndex + 1));
    lightbox.addEventListener('click', event => { if (event.target === lightbox) lightbox.close(); });
    lightbox.addEventListener('close', () => document.body.classList.remove('modal-open'));
    lightbox.addEventListener('keydown', event => {
      if (event.key === 'ArrowLeft') { event.preventDefault(); showImage(currentIndex - 1); }
      if (event.key === 'ArrowRight') { event.preventDefault(); showImage(currentIndex + 1); }
    });
  }

  const form = document.getElementById('enrollment-form');
  form?.addEventListener('submit', async event => {
    event.preventDefault();
    const submit = form.querySelector('[type=submit]');
    const status = form.querySelector('.form-status');
    const originalLabel = submit.innerHTML;
    submit.disabled = true;
    submit.textContent = 'Đang gửi đăng ký…';
    status.classList.remove('error');
    status.textContent = '';
    try {
      const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
      const data = await response.json().catch(() => ({}));
      if (!response.ok || !data.success) throw new Error(data.message || (response.status === 419 ? 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang rồi gửi lại.' : 'Chưa thể gửi đăng ký. Vui lòng thử lại sau.'));
      status.textContent = data.message;
      form.reset();
      submit.textContent = 'Đã gửi đăng ký thành công ✓';
    } catch (error) {
      status.classList.add('error');
      status.textContent = error.message === 'Failed to fetch' ? 'Không thể kết nối. Vui lòng kiểm tra mạng và thử lại.' : error.message;
      submit.disabled = false;
      submit.innerHTML = originalLabel;
    }
  });

  const sections = document.querySelectorAll('section[id]');
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) nav?.querySelectorAll('a').forEach(link => link.classList.toggle('active', link.hash === `#${entry.target.id}`));
      });
    }, { rootMargin: '-20% 0px -60% 0px' });
    sections.forEach(section => observer.observe(section));
  }

  document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', event => {
    if (!window.confirm(form.dataset.confirm)) event.preventDefault();
  }));
});
