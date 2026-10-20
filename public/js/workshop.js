document.addEventListener('DOMContentLoaded', () => {
  const menu = document.querySelector('.ws-menu');
  const nav = document.querySelector('.ws-nav');
  menu?.addEventListener('click', () => {
    const open = menu.getAttribute('aria-expanded') !== 'true';
    menu.setAttribute('aria-expanded', String(open));
    menu.setAttribute('aria-label', open ? 'Đóng menu' : 'Mở menu');
    nav?.classList.toggle('is-open', open);
  });
  const closeMenu = () => {
    menu?.setAttribute('aria-expanded', 'false');
    menu?.setAttribute('aria-label', 'Mở menu');
    nav?.classList.remove('is-open');
  };
  nav?.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
  document.addEventListener('keydown', event => { if (event.key === 'Escape') closeMenu(); });
  document.querySelector('[data-open-policy]')?.addEventListener('click', () => {
    const policy = document.querySelector('#registration-policy');
    if (policy) policy.open = true;
  });

  const cover = document.querySelector('.ws-cover');
  const floatingContact = document.querySelector('.ws-floating-contact');
  if (cover && floatingContact && 'IntersectionObserver' in window) {
    const coverObserver = new IntersectionObserver(entries => {
      floatingContact.hidden = entries[0].isIntersecting;
    });
    coverObserver.observe(cover);
  } else if (floatingContact) {
    floatingContact.hidden = false;
  }

  if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const observer = new IntersectionObserver(entries => entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    }), { threshold: .08 });
    document.documentElement.classList.add('ws-reveal-ready');
    document.querySelectorAll('[data-ws-reveal]').forEach(element => observer.observe(element));
  }

  const form = document.querySelector('#workshop-form');
  if (!form) return;
  const status = form.querySelector('.ws-form-status');
  const submit = form.querySelector('[type="submit"]');
  const fieldset = form.querySelector('fieldset');
  let submitting = false;
  form.addEventListener('submit', async event => {
    event.preventDefault();
    if (submitting || fieldset.disabled) return;
    submitting = true;
    const payload = new FormData(form);
    const original = submit.innerHTML;
    submit.disabled = true;
    submit.textContent = 'ĐANG GỬI ĐĂNG KÝ…';
    status.hidden = true;
    form.querySelectorAll('[aria-invalid]').forEach(input => input.removeAttribute('aria-invalid'));
    try {
      const response = await fetch(form.action, {
        method: 'POST', body: payload,
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
      });
      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        const firstField = Object.keys(data.errors || {})[0];
        if (firstField) {
          const input = form.elements.namedItem(firstField);
          input?.setAttribute('aria-invalid', 'true');
          input?.focus();
        }
        throw new Error(Object.values(data.errors || {})[0]?.[0] || (response.status === 419 ? 'Phiên đăng ký đã hết hạn. Vui lòng tải lại trang rồi gửi lại.' : response.status === 429 ? 'Bạn đã gửi nhiều yêu cầu. Vui lòng đợi một phút rồi thử lại.' : data.message || 'Chưa thể gửi đăng ký. Vui lòng thử lại.'));
      }
      status.classList.remove('is-error');
      status.textContent = data.message;
      status.hidden = false;
      status.focus();
      fieldset.disabled = true;
      submit.textContent = 'ĐÃ NHẬN ĐĂNG KÝ';
    } catch (error) {
      status.classList.add('is-error');
      status.textContent = error instanceof TypeError ? 'Không thể kết nối. Vui lòng kiểm tra mạng và thử lại.' : error.message;
      status.hidden = false;
      submit.disabled = false;
      submit.innerHTML = original;
    } finally {
      submitting = false;
    }
  });
});
