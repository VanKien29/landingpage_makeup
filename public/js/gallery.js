/* ============================================================
   GALLERY.JS — VAN CUONG MAKEUP & ACADEMY
   Portfolio Filtering & Modal Lightbox
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
  const filterBtns = document.querySelectorAll('.filter-btn');
  const portfolioItems = document.querySelectorAll('.portfolio-item');
  const lightbox = document.getElementById('lightbox');
  const lightboxImg = document.getElementById('lightbox-img');
  const lightboxClose = document.getElementById('lightbox-close');
  const lightboxPrev = document.getElementById('lightbox-prev');
  const lightboxNext = document.getElementById('lightbox-next');
  const lightboxCounter = document.getElementById('lightbox-counter');

  if (!portfolioItems.length) return;

  // ===== 1. Filter Logic =====
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('is-active'));
      btn.classList.add('is-active');

      const filter = btn.dataset.filter;

      portfolioItems.forEach(item => {
        const cat = item.dataset.category;
        if (filter === 'all' || cat === filter) {
          item.style.display = '';
          requestAnimationFrame(() => {
            item.style.opacity = '1';
            item.style.transform = 'scale(1)';
          });
        } else {
          item.style.opacity = '0';
          item.style.transform = 'scale(0.96)';
          setTimeout(() => {
            if (btn.dataset.filter !== 'all' && item.dataset.category !== btn.dataset.filter) {
              item.style.display = 'none';
            }
          }, 250);
        }
      });
    });
  });

  // ===== 2. Lightbox Logic =====
  if (!lightbox || !lightboxImg) return;

  let currentIndex = 0;
  const getVisibleItems = () => Array.from(portfolioItems).filter(item => item.style.display !== 'none');

  function updateLightbox(index) {
    const visible = getVisibleItems();
    if (!visible.length) return;
    
    if (index < 0) index = visible.length - 1;
    if (index >= visible.length) index = 0;
    
    currentIndex = index;
    const item = visible[currentIndex];
    const img = item.querySelector('img');
    
    if (img) {
      lightboxImg.src = img.src;
      lightboxImg.alt = img.alt || 'Văn Cường Makeup';
    }
    
    if (lightboxCounter) {
      lightboxCounter.textContent = `${currentIndex + 1} / ${visible.length}`;
    }
  }

  function openLightbox(index) {
    updateLightbox(index);
    lightbox.classList.add('is-open');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    lightbox.classList.remove('is-open');
    document.body.style.overflow = '';
  }

  portfolioItems.forEach((item) => {
    item.style.cursor = 'pointer';
    item.addEventListener('click', () => {
      const visible = getVisibleItems();
      const index = visible.indexOf(item);
      if (index !== -1) {
        openLightbox(index);
      }
    });
  });

  if (lightboxClose) {
    lightboxClose.addEventListener('click', closeLightbox);
  }

  lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) {
      closeLightbox();
    }
  });

  if (lightboxPrev) {
    lightboxPrev.addEventListener('click', (e) => {
      e.stopPropagation();
      updateLightbox(currentIndex - 1);
    });
  }

  if (lightboxNext) {
    lightboxNext.addEventListener('click', (e) => {
      e.stopPropagation();
      updateLightbox(currentIndex + 1);
    });
  }

  document.addEventListener('keydown', (e) => {
    if (!lightbox.classList.contains('is-open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') updateLightbox(currentIndex - 1);
    if (e.key === 'ArrowRight') updateLightbox(currentIndex + 1);
  });
});