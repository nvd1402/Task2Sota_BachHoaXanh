/**
 * File JavaScript tùy chỉnh
 */

// Code JavaScript tại đây

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

    const isDesktop = () => window.innerWidth >= 992; // ẩn trên mobile theo CSS

    const syncByScroll = () => {
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
    window.addEventListener('load', syncByScroll);
    window.addEventListener('scroll', syncByScroll, { passive: true });
    window.addEventListener('resize', syncByScroll);
})();

