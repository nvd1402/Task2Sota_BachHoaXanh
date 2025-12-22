<!-- Sidebar -->
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3" id="sidenav-main">
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
    <a class="navbar-brand m-0" href="index.php">
      <span class="ms-1 font-weight-bold">Admin Dashboard</span>
    </a>
  </div>
  <hr class="horizontal dark mt-0">
  <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" href="index.php">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-home text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'product') !== false ? 'active' : '' ?>" href="products.php">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-box text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Sản phẩm</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'categor') !== false ? 'active' : '' ?>" href="categories.php">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-tags text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Danh mục</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'brand') !== false ? 'active' : '' ?>" href="brands.php">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-certificate text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Thương hiệu</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'size') !== false ? 'active' : '' ?>" href="sizes.php">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-ruler text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Kích thước</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'order') !== false ? 'active' : '' ?>" href="orders.php">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-shopping-cart text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Đơn hàng</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'contact') !== false ? 'active' : '' ?>" href="contact.php">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-envelope text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Liên hệ</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'newsletter') !== false ? 'active' : '' ?>" href="newsletters.php">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-paper-plane text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Newsletter</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'news') !== false ? 'active' : '' ?>" href="news.php">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-newspaper text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Tin tức</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'recruitment') !== false ? 'active' : '' ?>" href="recruitment.php">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-briefcase text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Tuyển dụng</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'review') !== false ? 'active' : '' ?>" href="reviews.php">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-star text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Đánh giá</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'user') !== false ? 'active' : '' ?>" href="users.php">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-users text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Người dùng</span>
        </a>
      </li>
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Tài khoản</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="profile.php">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-user text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Hồ sơ</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="logout.php">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-sign-out-alt text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Đăng xuất</span>
        </a>
      </li>
    </ul>
  </div>
</aside>

