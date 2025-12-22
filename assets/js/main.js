/**
 * File JavaScript tùy chỉnh
 */

// User Dropdown - Sử dụng CSS hover, không cần JavaScript

// Code JavaScript tại đây

// Related Articles Carousel (News Detail Page)
(function() {
    const carouselWrapper = document.querySelector('.related-carousel-wrapper');
    if (!carouselWrapper) return;
    
    const container = carouselWrapper.querySelector('.related-carousel-container');
    const track = carouselWrapper.querySelector('.related-carousel-track');
    const prevBtn = carouselWrapper.querySelector('.related-prev');
    const nextBtn = carouselWrapper.querySelector('.related-next');
    const cards = carouselWrapper.querySelectorAll('.related-article-card');
    
    if (!container || !track || !prevBtn || !nextBtn || cards.length === 0) return;
    
    let currentIndex = 0;
    const cardsPerView = 3;
    
    const getCardWidth = () => {
        if (cards.length === 0) return 0;
        const containerWidth = container.offsetWidth;
        const gap = 24;
        const totalGaps = (cardsPerView - 1) * gap;
        return (containerWidth - totalGaps) / cardsPerView;
    };
    
    const scrollByCards = (direction) => {
        const totalCards = cards.length;
        const maxIndex = Math.max(0, totalCards - cardsPerView);
        
        if (direction === 'next') {
            currentIndex = Math.min(currentIndex + cardsPerView, maxIndex);
        } else {
            currentIndex = Math.max(currentIndex - cardsPerView, 0);
        }
        
        const cardWidth = getCardWidth();
        const gap = 24;
        const translateX = currentIndex * (cardWidth + gap);
        
        track.style.transform = `translateX(-${translateX}px)`;
    };
    
    prevBtn.addEventListener('click', () => scrollByCards('prev'));
    nextBtn.addEventListener('click', () => scrollByCards('next'));
    
    // Drag/Swipe functionality
    let isDown = false;
    let startX = 0;
    let startTranslateX = 0;
    
    const handleMouseDown = (e) => {
        isDown = true;
        startX = e.pageX;
        const cardWidth = getCardWidth();
        const gap = 24;
        startTranslateX = currentIndex * (cardWidth + gap);
        track.style.cursor = 'grabbing';
        track.style.transition = 'none';
    };
    
    const handleMouseLeave = () => {
        if (isDown) {
            isDown = false;
            track.style.cursor = 'grab';
            track.style.transition = 'transform 0.3s ease';
            // Snap to nearest position
            const cardWidth = getCardWidth();
            const gap = 24;
            const currentTranslateX = parseFloat(track.style.transform.replace('translateX(-', '').replace('px)', '')) || 0;
            currentIndex = Math.round(currentTranslateX / (cardWidth + gap));
            const maxIndex = Math.max(0, cards.length - cardsPerView);
            currentIndex = Math.max(0, Math.min(currentIndex, maxIndex));
            track.style.transform = `translateX(-${currentIndex * (cardWidth + gap)}px)`;
        }
    };
    
    const handleMouseUp = () => {
        if (isDown) {
            isDown = false;
            track.style.cursor = 'grab';
            track.style.transition = 'transform 0.3s ease';
            // Snap to nearest position
            const cardWidth = getCardWidth();
            const gap = 24;
            const currentTranslateX = parseFloat(track.style.transform.replace('translateX(-', '').replace('px)', '')) || 0;
            currentIndex = Math.round(currentTranslateX / (cardWidth + gap));
            const maxIndex = Math.max(0, cards.length - cardsPerView);
            currentIndex = Math.max(0, Math.min(currentIndex, maxIndex));
            track.style.transform = `translateX(-${currentIndex * (cardWidth + gap)}px)`;
        }
    };
    
    const handleMouseMove = (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - track.offsetLeft;
        const walk = (x - startX) * 2;
        const cardWidth = getCardWidth();
        const gap = 24;
        const newTranslateX = (currentIndex * (cardWidth + gap)) - walk;
        const maxTranslateX = Math.max(0, (cards.length - cardsPerView) * (cardWidth + gap));
        const clampedTranslateX = Math.max(0, Math.min(newTranslateX, maxTranslateX));
        track.style.transform = `translateX(-${clampedTranslateX}px)`;
    };
    
    // Touch events
    let touchStartX = 0;
    let touchStartTranslateX = 0;
    
    const handleTouchStart = (e) => {
        touchStartX = e.touches[0].pageX;
        const cardWidth = getCardWidth();
        const gap = 24;
        touchStartTranslateX = currentIndex * (cardWidth + gap);
    };
    
    const handleTouchMove = (e) => {
        if (!touchStartX) return;
        const x = e.touches[0].pageX;
        const walk = (touchStartX - x) * 2;
        const cardWidth = getCardWidth();
        const gap = 24;
        const newTranslateX = touchStartTranslateX + walk;
        const maxTranslateX = Math.max(0, (cards.length - cardsPerView) * (cardWidth + gap));
        const clampedTranslateX = Math.max(0, Math.min(newTranslateX, maxTranslateX));
        track.style.transform = `translateX(-${clampedTranslateX}px)`;
    };
    
    const handleTouchEnd = () => {
        if (!touchStartX) return;
        const cardWidth = getCardWidth();
        const gap = 24;
        const currentTranslateX = parseFloat(track.style.transform.replace('translateX(-', '').replace('px)', '')) || 0;
        currentIndex = Math.round(currentTranslateX / (cardWidth + gap));
        const maxIndex = Math.max(0, cards.length - cardsPerView);
        currentIndex = Math.max(0, Math.min(currentIndex, maxIndex));
        track.style.transform = `translateX(-${currentIndex * (cardWidth + gap)}px)`;
        touchStartX = 0;
    };
    
    // Prevent image dragging
    const images = track.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('dragstart', (e) => e.preventDefault());
        img.addEventListener('mousedown', (e) => e.preventDefault());
        img.style.userSelect = 'none';
        img.style.webkitUserDrag = 'none';
    });
    
    // Desktop mouse events
    track.addEventListener('mousedown', handleMouseDown);
    track.addEventListener('mouseleave', handleMouseLeave);
    track.addEventListener('mouseup', handleMouseUp);
    track.addEventListener('mousemove', handleMouseMove);
    
    // Touch events
    track.addEventListener('touchstart', handleTouchStart, { passive: true });
    track.addEventListener('touchmove', handleTouchMove, { passive: true });
    track.addEventListener('touchend', handleTouchEnd);
    
    track.style.cursor = 'grab';
    
    // Initialize: ensure 3 cards are visible
    const initCarousel = () => {
        const cardWidth = getCardWidth();
        const gap = 24;
        // Force cards to have correct width
        cards.forEach(card => {
            card.style.width = `${cardWidth}px`;
            card.style.minWidth = `${cardWidth}px`;
            card.style.maxWidth = `${cardWidth}px`;
        });
        track.style.transform = `translateX(-${currentIndex * (cardWidth + gap)}px)`;
    };
    
    // Initialize on load
    window.addEventListener('load', initCarousel);
    // Also initialize immediately
    setTimeout(initCarousel, 100);
    
    // Update on resize
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            initCarousel();
        }, 250);
    });
})();

// Hiển thị header + nav khi cuộn xuống một chút
(function() {
    const toggleSticky = () => {
        if (window.scrollY > 20) {
            document.body.classList.add('sticky-show');
        } else {
            document.body.classList.remove('sticky-show');
        }
    };
    window.addEventListener('scroll', toggleSticky, { passive: true });
    window.addEventListener('load', toggleSticky);
})();

// Hiệu ứng: khi cuộn qua flash-sale, ẩn header + nav 1s rồi hiện lại
(function() {
    const FLASH_HEIGHT = 38; // px, khớp với var --flash-height
    let hiddenOnce = false;
    let timer = null;

    const handleFlashPass = () => {
        const y = window.scrollY || window.pageYOffset;
        if (y > FLASH_HEIGHT && !hiddenOnce) {
            hiddenOnce = true;
            document.body.classList.add('hide-header');
            clearTimeout(timer);
            timer = setTimeout(() => {
                document.body.classList.remove('hide-header');
            }, 1000);
        }
        if (y <= FLASH_HEIGHT) {
            hiddenOnce = false;
            clearTimeout(timer);
            document.body.classList.remove('hide-header');
        }
    };

    window.addEventListener('scroll', handleFlashPass, { passive: true });
    window.addEventListener('load', handleFlashPass);
})();

// Toggle danh mục sản phẩm (mock data)
(function() {
    const toggle = document.querySelector('.category-toggle');
    const dropdown = document.querySelector('.category-dropdown');
    if (!toggle || !dropdown) return;

    const closeDropdown = () => dropdown.classList.remove('show');
    const openDropdown = () => dropdown.classList.add('show');

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('show');
    });

    // Không đóng khi click ngoài, chỉ đóng khi toggle hoặc cuộn xuống (logic phía dưới)
})();

// Nút trở về đầu trang
(function() {
    const btn = document.querySelector('.back-to-top');
    if (!btn) return;

    const toggleBtn = () => {
        if (window.scrollY > 200) {
            btn.classList.add('show');
        } else {
            btn.classList.remove('show');
        }
    };

    btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    window.addEventListener('scroll', toggleBtn, { passive: true });
    window.addEventListener('load', toggleBtn);
})();

// Carousel cho "Sản phẩm nổi bật" (nút + kéo/ vuốt)
(function() {
    const track = document.getElementById('featuredTrack');
    const prevBtn = document.getElementById('featuredPrev');
    const nextBtn = document.getElementById('featuredNext');
    if (!track || !prevBtn || !nextBtn) return;

    const container = track.parentElement; // .featured-grid

    const scrollByCards = (direction) => {
        const cards = track.querySelectorAll('.featured-card');
        if (!cards.length) return;
        const cardWidth = cards[0].offsetWidth + 16; // 16 = gap
        const perView = window.innerWidth >= 992 ? 5 : 2;
        const delta = cardWidth * perView * direction;
        track.parentElement.scrollBy({ left: delta, behavior: 'smooth' });
    };

    prevBtn.addEventListener('click', () => scrollByCards(-1));
    nextBtn.addEventListener('click', () => scrollByCards(1));

    // Drag với chuột
    let isMouseDown = false;
    let startX = 0;
    let startY = 0;
    let startScrollLeft = 0;

    container.addEventListener('mousedown', (e) => {
        if (e.button !== 0) return;
        isMouseDown = true;
        startX = e.clientX;
        startY = e.clientY;
        startScrollLeft = container.scrollLeft;
        container.classList.add('dragging');
        e.preventDefault(); // tránh kéo ảnh
    });

    window.addEventListener('mousemove', (e) => {
        if (!isMouseDown) return;
        const dx = e.clientX - startX;
        const dy = e.clientY - startY;
        // Nếu kéo dọc nhiều hơn thì bỏ, để trang cuộn bình thường
        if (Math.abs(dy) > Math.abs(dx)) {
            isMouseDown = false;
            container.classList.remove('dragging');
            return;
        }
        container.scrollLeft = startScrollLeft - dx;
    });

    window.addEventListener('mouseup', () => {
        if (!isMouseDown) return;
        isMouseDown = false;
        container.classList.remove('dragging');
    });

    // Vuốt trên điện thoại
    let touchStartX = 0;
    let touchStartY = 0;
    let touchScrollLeft = 0;
    let isTouching = false;

    container.addEventListener('touchstart', (e) => {
        if (!e.touches || e.touches.length !== 1) return;
        const t = e.touches[0];
        isTouching = true;
        touchStartX = t.clientX;
        touchStartY = t.clientY;
        touchScrollLeft = container.scrollLeft;
    }, { passive: true });

    container.addEventListener('touchmove', (e) => {
        if (!isTouching || !e.touches || e.touches.length !== 1) return;
        const t = e.touches[0];
        const dx = t.clientX - touchStartX;
        const dy = t.clientY - touchStartY;
        // Nếu kéo dọc thì trả quyền cho cuộn trang
        if (Math.abs(dy) > Math.abs(dx)) {
            isTouching = false;
            return;
        }
        container.scrollLeft = touchScrollLeft - dx;
    }, { passive: true });

    container.addEventListener('touchend', () => {
        isTouching = false;
    }, { passive: true });

    window.addEventListener('resize', () => {
        // force reflow to cập nhật cardWidth trên thay đổi kích thước
        void track.offsetWidth;
    });
})();

// Cho phép lướt (swipe) banner carousel trên mobile
(function() {
    const carouselEl = document.querySelector('#heroCarousel');
    if (!carouselEl || typeof bootstrap === 'undefined') return;
    const carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);

    let startX = 0;
    let startY = 0;
    let isMoving = false;

    const onTouchStart = (e) => {
        if (!e.touches || e.touches.length !== 1) return;
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        isMoving = true;
    };

    const onTouchMove = (e) => {
        if (!isMoving || !e.touches || e.touches.length !== 1) return;
        const dx = e.touches[0].clientX - startX;
        const dy = e.touches[0].clientY - startY;
        // Ngưỡng và loại trừ cuộn dọc
        if (Math.abs(dy) > Math.abs(dx)) {
            isMoving = false;
            return;
        }
        if (Math.abs(dx) > 50) {
            if (dx > 0) {
                carousel.prev();
            } else {
                carousel.next();
            }
            isMoving = false;
        }
    };

    const onTouchEnd = () => {
        isMoving = false;
    };

    carouselEl.addEventListener('touchstart', onTouchStart, { passive: true });
    carouselEl.addEventListener('touchmove', onTouchMove, { passive: true });
    carouselEl.addEventListener('touchend', onTouchEnd, { passive: true });
    carouselEl.addEventListener('touchcancel', onTouchEnd, { passive: true });
})();

// Drag bằng chuột trên desktop cho carousel
(function() {
    const carouselEl = document.querySelector('#heroCarousel');
    if (!carouselEl || typeof bootstrap === 'undefined') return;
    const carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);

    let isDown = false;
    let startX = 0;
    let startY = 0;

    const onMouseDown = (e) => {
        if (e.button !== 0) return; // chỉ chuột trái
        isDown = true;
        startX = e.clientX;
        startY = e.clientY;
        carouselEl.classList.add('dragging');
        e.preventDefault();
    };

    const onMouseMove = (e) => {
        if (!isDown) return;
        const dx = e.clientX - startX;
        const dy = e.clientY - startY;
        if (Math.abs(dy) > Math.abs(dx)) {
            isDown = false;
            carouselEl.classList.remove('dragging');
            return;
        }
        if (Math.abs(dx) > 50) {
            if (dx > 0) {
                carousel.prev();
            } else {
                carousel.next();
            }
            isDown = false;
            carouselEl.classList.remove('dragging');
        }
    };

    const onMouseUp = () => {
        if (isDown) {
            isDown = false;
            carouselEl.classList.remove('dragging');
        }
    };

    carouselEl.addEventListener('mousedown', onMouseDown);
    carouselEl.addEventListener('dragstart', (e) => e.preventDefault());
    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mouseup', onMouseUp);
})();

// Dropdown danh mục: mặc định mở khi vào trang và khi ở top; cuộn xuống thì thu lại
(function() {
    const toggle = document.querySelector('.category-toggle');
    const dropdown = document.querySelector('.category-dropdown');
    if (!toggle || !dropdown) return;

    const isHome = document.body.classList.contains('page-index'); // chỉ auto mở trên trang chủ
    const isDesktop = () => window.innerWidth >= 992; // ẩn trên mobile theo CSS

    const syncByScroll = () => {
        if (!isHome) return; // các trang khác không tự xổ danh mục
        if (!isDesktop()) {
            dropdown.classList.remove('show');
            return;
        }
        const atTop = (window.scrollY || window.pageYOffset) <= 80;
        if (atTop) {
            dropdown.classList.add('show');
        } else {
            dropdown.classList.remove('show');
        }
    };

    // Mặc định mở khi load (desktop)
    if (isHome) {
        window.addEventListener('load', syncByScroll);
        window.addEventListener('scroll', syncByScroll, { passive: true });
        window.addEventListener('resize', syncByScroll);
    }
})();

(function () {
    const track = document.getElementById('newsTrack');
    const prevBtn = document.getElementById('newsPrev');
    const nextBtn = document.getElementById('newsNext');

    if (!track || !prevBtn || !nextBtn) return;

    let index = 0;
    const GAP = 24;

    function getCardWidth() {
        if (!track.children.length) return 0;
        return track.children[0].offsetWidth + GAP;
    }

    function updatePosition() {
        track.style.transform = `translateX(-${index * getCardWidth()}px)`;
    }

    prevBtn.addEventListener('click', function () {
        index = Math.max(index - 1, 0);
        updatePosition();
    });

    nextBtn.addEventListener('click', function () {
        const maxIndex = track.children.length - 1;
        index = Math.min(index + 1, maxIndex);
        updatePosition();
    });

    // Reset khi resize để không lệch layout responsive
    window.addEventListener('resize', function () {
        index = 0;
        updatePosition();
    });
})();