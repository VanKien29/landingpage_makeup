document.addEventListener('DOMContentLoaded', () => {
  let selectSequence = 0;
  const enhanceSelects = root => root.querySelectorAll('select:not([data-select-enhanced])').forEach(select => {
    select.dataset.selectEnhanced = '';
    const wrapper = document.createElement('div'); wrapper.className = 'admin-custom-select';
    select.before(wrapper); wrapper.append(select); select.classList.add('admin-native-select');
    const button = document.createElement('button'); button.type = 'button'; button.className = 'admin-select-trigger';
    const list = document.createElement('div'); list.id = `admin-options-${++selectSequence}`; list.className = 'admin-select-options'; list.hidden = true; list.setAttribute('role', 'listbox');
    button.setAttribute('role', 'combobox'); button.setAttribute('aria-haspopup', 'listbox'); button.setAttribute('aria-controls', list.id); button.setAttribute('aria-expanded', 'false');
    const label = select.labels?.[0]?.textContent?.trim() || select.getAttribute('aria-label') || 'Chọn giá trị';
    button.setAttribute('aria-label', label);
    const sync = () => {
      button.textContent = select.selectedOptions[0]?.textContent || 'Chọn giá trị';
      button.disabled = select.disabled;
      list.querySelectorAll('[role=option]').forEach(option => option.setAttribute('aria-selected', String(option.dataset.value === select.value)));
    };
    const close = () => { list.hidden = true; button.setAttribute('aria-expanded', 'false'); };
    const open = () => {
      document.querySelectorAll('.admin-select-options:not([hidden])').forEach(other => {
        if (other !== list) { other.hidden = true; other.parentElement.querySelector('.admin-select-trigger').setAttribute('aria-expanded', 'false'); }
      });
      list.hidden = false; button.setAttribute('aria-expanded', 'true');
      list.querySelector('[aria-selected=true]:not(:disabled)')?.focus();
    };
    [...select.options].forEach(option => {
      const item = document.createElement('button'); item.type = 'button'; item.setAttribute('role', 'option'); item.tabIndex = -1; item.dataset.value = option.value;
      item.textContent = option.textContent; item.disabled = option.disabled;
      item.addEventListener('click', () => { select.value = option.value; select.dispatchEvent(new Event('change', {bubbles:true})); close(); button.focus(); });
      list.append(item);
    });
    wrapper.append(button, list); sync();
    [...(select.labels || [])].forEach(labelElement => labelElement.addEventListener('click', event => {
      if (window.matchMedia('(min-width:761px)').matches) { event.preventDefault(); button.focus(); }
    }));
    button.addEventListener('click', () => list.hidden ? open() : close());
    select.addEventListener('change', sync);
    const validationHint = document.createElement('small'); validationHint.className = 'admin-inline-error'; validationHint.hidden = true; validationHint.id = `${list.id}-error`; validationHint.setAttribute('role', 'alert'); wrapper.append(validationHint);
    select.addEventListener('invalid', event => {
      if (window.matchMedia('(min-width:761px)').matches) {
        event.preventDefault(); button.focus(); button.setAttribute('aria-invalid', 'true'); button.setAttribute('aria-describedby', validationHint.id);
        validationHint.textContent = `Vui lòng chọn ${label.toLocaleLowerCase('vi').replace(/\s*\*$/, '')}.`; validationHint.hidden = false;
      }
    });
    select.addEventListener('change', () => { button.removeAttribute('aria-invalid'); button.removeAttribute('aria-describedby'); validationHint.hidden = true; });
    wrapper.addEventListener('keydown', event => {
      if (event.target === select) return;
      const options = [...list.querySelectorAll('[role=option]:not(:disabled)')];
      const activeIndex = options.indexOf(document.activeElement);
      if (event.key === 'Escape') { close(); button.focus(); event.stopPropagation(); event.preventDefault(); }
      else if (event.key === 'Tab') close();
      else if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
        event.preventDefault();
        if (list.hidden) { open(); return; }
        const next = event.key === 'Home' ? 0 : event.key === 'End' ? options.length - 1 : (activeIndex + (event.key === 'ArrowDown' ? 1 : -1) + options.length) % options.length;
        options[next]?.focus();
      } else if (event.key.length === 1 && !event.ctrlKey && !event.metaKey && event.key !== ' ') {
        const found = options.find(item => item.textContent.toLocaleLowerCase('vi').startsWith(event.key.toLocaleLowerCase('vi')));
        if (found) { event.preventDefault(); if (list.hidden) open(); found.focus(); }
      }
    });
    select.form?.addEventListener('reset', () => requestAnimationFrame(sync));
  });
  document.addEventListener('click', event => {
    document.querySelectorAll('.admin-select-options:not([hidden])').forEach(list => {
      if (!list.parentElement.contains(event.target)) {
        list.hidden = true;
        list.parentElement.querySelector('.admin-select-trigger').setAttribute('aria-expanded', 'false');
      }
    });
  });
  const closeDialog = dialog => { if (dialog?.open) dialog.close(); };
  document.querySelectorAll('dialog').forEach(dialog => {
    dialog.querySelectorAll('[data-dialog-close], [data-close-capacity-dialog]').forEach(button => button.addEventListener('click', () => closeDialog(dialog)));
    dialog.addEventListener('click', event => {
      if (event.target !== dialog) return;
      const bounds = dialog.getBoundingClientRect();
      if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) closeDialog(dialog);
    });
  });
  document.querySelectorAll('[data-open-dialog]').forEach(button => button.addEventListener('click', () => {
    const dialog = document.getElementById(button.dataset.openDialog);
    if (dialog && !dialog.open) dialog.showModal();
  }));
  const capacityDialog = document.querySelector('[data-capacity-dialog]');
  document.querySelector('[data-open-capacity-dialog]')?.addEventListener('click', () => capacityDialog?.showModal());

  const confirmDialog = document.querySelector('[data-confirm-dialog]');
  let pendingForm = null;
  let pendingSubmitter = null;
  const approvedForms = new WeakSet();
  document.addEventListener('submit', event => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm')) return;
    if (approvedForms.has(form)) { approvedForms.delete(form); return; }
    event.preventDefault();
    if (!confirmDialog || confirmDialog.open) return;
    pendingForm = form;
    pendingSubmitter = event.submitter;
    const selected = form.hasAttribute('data-bulk-form') ? [...document.querySelectorAll('[data-item-select]:checked')] : [];
    const action = form.elements.namedItem('action')?.value;
    if (form.hasAttribute('data-bulk-form') && selected.length === 0) return;
    const isDelete = action === 'delete' || form.hasAttribute('data-confirm-danger');
    const message = form.hasAttribute('data-bulk-form')
      ? `${{delete: 'Xóa', show: 'Hiển thị', hide: 'Ẩn'}[action]} ${selected.length} tác phẩm đã chọn?${isDelete ? ' Ảnh tải lên sẽ bị xóa khỏi hệ thống. Thao tác này không thể hoàn tác.' : ''}`
      : form.dataset.confirm;
    confirmDialog.querySelector('#admin-confirm-message').textContent = message;
    const accept = confirmDialog.querySelector('[data-confirm-accept]');
    accept.classList.toggle('danger', isDelete);
    accept.textContent = isDelete ? (action === 'cancel' ? 'Xác nhận từ chối' : 'Xác nhận xóa') : 'Xác nhận';
    confirmDialog.showModal();
  });
  confirmDialog?.querySelector('[data-confirm-accept]')?.addEventListener('click', () => {
    const form = pendingForm;
    const submitter = pendingSubmitter;
    closeDialog(confirmDialog);
    if (!form) return;
    approvedForms.add(form);
    if (submitter?.isConnected) form.requestSubmit(submitter); else form.requestSubmit();
  });
  confirmDialog?.addEventListener('close', () => { pendingForm = null; pendingSubmitter = null; });

  document.querySelectorAll('[data-layout-scope]').forEach(section => {
    const key = `academy-admin-layout-${section.dataset.layoutScope}`;
    const mobile = window.matchMedia('(max-width: 760px)');
    let preferred = null;
    const storageKey = () => `${key}-${mobile.matches ? 'mobile' : 'desktop'}`;
    const readPreference = () => {
      preferred = null;
      try { const saved = localStorage.getItem(storageKey()); if (saved === 'table' || saved === 'cards') preferred = saved; } catch { /* The responsive default remains available when storage is blocked. */ }
    };
    readPreference();
    const apply = () => {
      const mode = preferred || (mobile.matches ? 'cards' : 'table');
      section.dataset.layout = mode;
      section.querySelectorAll('[data-set-layout]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.setLayout === mode)));
    };
    section.querySelectorAll('[data-set-layout]').forEach(button => button.addEventListener('click', () => {
      preferred = button.dataset.setLayout;
      try { localStorage.setItem(storageKey(), preferred); } catch { /* Keep the current choice for this page. */ }
      apply();
    }));
    mobile.addEventListener('change', () => { readPreference(); apply(); });
    apply();
  });

  const bulk = document.querySelector('[data-bulk-form]');
  if (bulk) {
    const all = bulk.querySelector('[data-select-all]');
    const items = [...document.querySelectorAll('[data-item-select]')];
    const updateSelection = () => {
      const checked = items.filter(input => input.checked).length;
      all.checked = items.length > 0 && checked === items.length;
      all.indeterminate = checked > 0 && checked < items.length;
      all.disabled = items.length === 0;
      bulk.querySelector('[data-selected-count]').textContent = `Đã chọn ${checked}`;
      bulk.querySelector('[data-bulk-submit]').disabled = checked === 0;
      items.forEach(input => input.closest('tr').classList.toggle('is-selected', input.checked));
    };
    all.addEventListener('change', () => { items.forEach(input => { input.checked = all.checked; }); updateSelection(); });
    items.forEach(input => input.addEventListener('change', updateSelection));
    bulk.querySelector('[data-clear-selection]').addEventListener('click', () => { items.forEach(input => { input.checked = false; }); updateSelection(); });
    updateSelection();
  }

  const imageDialog = document.querySelector('[data-image-dialog]');
  const fullImage = imageDialog?.querySelector('[data-image-full]');
  let zoom = 1;
  let fittedWidth = 0;
  const applyZoom = () => {
    if (!fullImage) return;
    if (zoom === 1) { fullImage.style.width = ''; fullImage.style.maxWidth = '100%'; fullImage.style.maxHeight = '100%'; }
    else {
      fullImage.style.width = `${Math.max(fittedWidth || fullImage.clientWidth, 1) * zoom}px`;
      fullImage.style.maxWidth = 'none';
      fullImage.style.maxHeight = 'none';
    }
    imageDialog.querySelector('[data-image-zoom-label]').textContent = `${Math.round(zoom * 100)}%`;
    imageDialog.querySelector('[data-image-zoom-out]').disabled = zoom === 1;
    imageDialog.querySelector('[data-image-zoom-in]').disabled = zoom >= 3;
  };
  document.addEventListener('click', event => {
    const trigger = event.target.closest('[data-image-open]');
    if (!trigger || !imageDialog) return;
    event.preventDefault();
    const source = trigger.getAttribute('href');
    if (!source) return;
    fullImage.src = source;
    fullImage.alt = trigger.dataset.imageTitle || trigger.querySelector('img')?.alt || 'Hình ảnh tác phẩm';
    imageDialog.querySelector('#admin-image-title').textContent = fullImage.alt;
    imageDialog.querySelector('[data-image-details]').textContent = trigger.dataset.imageDetails || '';
    imageDialog.querySelector('[data-image-original]').href = source;
    zoom = 1;
    fittedWidth = 0;
    if (!imageDialog.open) imageDialog.showModal();
    applyZoom();
    if (fullImage.complete) fittedWidth = fullImage.clientWidth;
  });
  fullImage?.addEventListener('load', () => { if (zoom === 1) fittedWidth = fullImage.clientWidth; });
  imageDialog?.querySelector('[data-image-zoom-in]').addEventListener('click', () => { zoom = Math.min(3, zoom + .5); applyZoom(); });
  imageDialog?.querySelector('[data-image-zoom-out]').addEventListener('click', () => { zoom = Math.max(1, zoom - .5); applyZoom(); });
  imageDialog?.addEventListener('close', () => { fullImage.removeAttribute('src'); });

  const uploadForm = document.querySelector('[data-upload-form]');
  if (uploadForm) {
    const input = uploadForm.querySelector('[data-upload-input]');
    const previews = uploadForm.querySelector('[data-upload-previews]');
    const error = uploadForm.querySelector('[data-upload-error]');
    const heading = uploadForm.querySelector('[data-upload-heading]');
    let files = [];
    let urls = [];
    let metadata = [];
    try { metadata = JSON.parse(uploadForm.querySelector('[data-upload-old]').textContent || '[]'); if (!Array.isArray(metadata)) metadata = []; } catch { metadata = []; }
    const defaults = index => ({
      title: `${uploadForm.elements.namedItem('title').value.trim() || files[index]?.name.replace(/\.[^.]+$/, '') || 'Tác phẩm'}${files.length > 1 ? ` ${String(index + 1).padStart(2, '0')}` : ''}`,
      category: uploadForm.elements.namedItem('category').value,
      alt_text: uploadForm.elements.namedItem('alt_text').value,
      display_order: Math.min(10000, Number(uploadForm.elements.namedItem('display_order').value || 0) + index),
      is_featured: uploadForm.querySelector('input[type=checkbox][name=is_featured]').checked ? '1' : '0',
    });
    const readMetadata = () => [...previews.querySelectorAll('[data-upload-item]')].map(row => {
      const entry = {};
      row.querySelectorAll('[data-metadata-field]').forEach(field => { entry[field.dataset.metadataField] = field.value; });
      return entry;
    });
    const field = (row, index, key, labelText, value, type = 'text') => {
      const wrap = document.createElement('div'); wrap.className = 'admin-field';
      const id = `upload-${index}-${key}`;
      const label = document.createElement('label'); label.htmlFor = id; label.textContent = labelText;
      const control = document.createElement(type === 'select' ? 'select' : 'input');
      control.id = id; control.name = `items[${index}][${key}]`; control.dataset.metadataField = key;
      if (type === 'select') {
        const options = key === 'category'
          ? [...uploadForm.querySelector('#category').options].map(option => [option.value, option.textContent])
          : [['1', 'Hiển thị trên website'], ['0', 'Ẩn khỏi website']];
        options.forEach(([optionValue, text]) => control.add(new Option(text, optionValue)));
      } else { control.type = type; control.maxLength = 255; }
      if (key === 'display_order') { control.min = '0'; control.max = '10000'; }
      if (key !== 'alt_text') control.required = true;
      control.value = value ?? '';
      wrap.append(label, control); row.append(wrap);
    };
    const render = () => {
      urls.forEach(url => URL.revokeObjectURL(url)); urls = [];
      previews.replaceChildren();
      files.forEach((file, index) => {
        const data = metadata[index] || defaults(index);
        const row = document.createElement('article'); row.className = 'admin-upload-item'; row.dataset.uploadItem = '';
        const header = document.createElement('div'); header.className = 'admin-upload-item-header';
        const link = document.createElement('a'); link.dataset.imageOpen = ''; link.dataset.imageTitle = file.name;
        const url = URL.createObjectURL(file); urls.push(url); link.href = url;
        const img = document.createElement('img'); img.src = url; img.alt = file.name; link.append(img);
        const info = document.createElement('div'); const name = document.createElement('p'); name.textContent = file.name;
        const size = document.createElement('small'); size.textContent = `Ảnh ${index + 1} · ${(file.size / 1024 / 1024).toFixed(2)} MB`; info.append(name, size);
        const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'admin-icon-button danger'; remove.textContent = '×'; remove.setAttribute('aria-label', `Bỏ ảnh ${file.name}`);
        remove.addEventListener('click', () => {
          metadata = readMetadata(); metadata.splice(index, 1); files.splice(index, 1);
          const transfer = new DataTransfer(); files.forEach(current => transfer.items.add(current)); input.files = transfer.files;
          render();
        });
        header.append(link, info, remove); row.append(header);
        const grid = document.createElement('div'); grid.className = 'admin-form-grid';
        field(grid, index, 'title', 'Tên tác phẩm *', data.title);
        field(grid, index, 'category', 'Danh mục *', data.category, 'select');
        field(grid, index, 'alt_text', 'Mô tả ảnh', data.alt_text);
        field(grid, index, 'display_order', 'Thứ tự hiển thị *', data.display_order, 'number');
        field(grid, index, 'is_featured', 'Trạng thái hiển thị *', data.is_featured, 'select');
        row.append(grid); previews.append(row);
      });
      heading.hidden = files.length === 0;
      uploadForm.querySelector('[data-upload-count]').textContent = `(${files.length} ảnh)`;
      uploadForm.querySelector('#title').required = files.length === 0;
      uploadForm.querySelector('#category').required = files.length === 0;
      enhanceSelects(previews);
    };
    input.addEventListener('change', () => {
      if (previews.children.length) metadata = readMetadata();
      const chosen = [...input.files];
      const invalid = chosen.find(file => !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 8 * 1024 * 1024);
      const message = chosen.length > 20 ? 'Mỗi lần chỉ được tải tối đa 20 ảnh.' : invalid ? `Ảnh “${invalid.name}” không hợp lệ. Chỉ dùng JPG, PNG, WebP và tối đa 8 MB/ảnh.` : '';
      error.textContent = message; error.hidden = !message; input.setCustomValidity(message);
      if (message) { files = []; input.value = ''; render(); return; }
      files = chosen;
      render();
    });
    uploadForm.querySelector('[data-apply-defaults]').addEventListener('click', () => { metadata = files.map((_, index) => defaults(index)); render(); });
    uploadForm.addEventListener('submit', () => { metadata = readMetadata(); });
  }
  const replacementInput = document.querySelector('[data-replacement-input]');
  let replacementUrl = null;
  replacementInput?.addEventListener('change', () => {
    const image = document.querySelector('[data-replacement-preview]');
    if (replacementUrl) URL.revokeObjectURL(replacementUrl);
    const file = replacementInput.files[0];
    const invalid = file && (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 8 * 1024 * 1024);
    replacementInput.setCustomValidity(invalid ? 'Chỉ dùng ảnh JPG, PNG, WebP và tối đa 8 MB.' : '');
    image.hidden = !file || invalid;
    if (file && !invalid) { replacementUrl = URL.createObjectURL(file); image.src = replacementUrl; }
    if (invalid) replacementInput.reportValidity();
  });
  enhanceSelects(document);
  document.querySelectorAll('dialog[data-auto-open]').forEach(dialog => { if (!dialog.open) dialog.showModal(); });
});
