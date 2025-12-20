<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = "Tuyển dụng - Bách Hóa Xanh";

// Kết nối database
$conn = connectDB();

// Lấy trang hiện tại
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Lấy danh sách tuyển dụng
$recruitmentData = getRecruitment($conn, ['page' => $page, 'per_page' => 10, 'status' => 'open']);
$jobs = $recruitmentData['recruitment'];
$totalPages = $recruitmentData['total_pages'];
$currentPage = $recruitmentData['current_page'];

include 'includes/header.php';
?>

<main class="recruit-page">
    <div class="container recruit-layout">
        <?php
        if (empty($jobs)) {
            echo '<p class="text-center">Hiện tại không có vị trí tuyển dụng nào.</p>';
        } else {
            foreach ($jobs as $job): 
                $desc = !empty($job['description']) ? mb_substr(strip_tags($job['description']), 0, 100) . '...' : '';
                ?>
                <a href="recruitment-detail.php?slug=<?= htmlspecialchars($job['slug']) ?>" class="recruit-card">
                    <div class="recruit-card-content">
                        <span class="recruit-title"><?= htmlspecialchars($job['title']) ?></span>
                        <span class="recruit-desc"><?= htmlspecialchars($desc) ?></span>
                    </div>
                </a>
            <?php endforeach;
        }
        ?>

        <aside class="recruit-sidebar">
            <div class="recruit-widget">
                <h4>CHUYÊN MỤC TIN TỨC</h4>
                <ul class="recruit-cats">
                    <li><a href="#">Thời trang &amp; cuộc sống</a></li>
                    <li><a href="#">Tin công nghệ</a></li>
                    <li><a href="#">Tin tức</a></li>
                    <li class="active"><a href="#">Tuyển dụng</a></li>
                </ul>
            </div>

            <div class="recruit-widget">
                <h4>TIN TỨC MỚI NHẤT</h4>
                <div class="recruit-latest">
                    <?php
                    // Lấy 5 tin tức mới nhất
                    $latestData = getNews($conn, ['per_page' => 5, 'status' => 'published']);
                    $latest = $latestData['news'];
                    
                    if (empty($latest)) {
                        echo '<p class="text-muted">Chưa có tin tức nào.</p>';
                    } else {
                        foreach ($latest as $item): 
                            $imgPath = !empty($item['featured_image']) ? 'assets/images/' . $item['featured_image'] : 'assets/images/lesterblur__2.jpg';
                            ?>
                            <a href="news-detail.php?slug=<?= htmlspecialchars($item['slug']) ?>" class="latest-item">
                                <div class="latest-thumb">
                                    <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                </div>
                                <div class="latest-info">
                                    <p><?= htmlspecialchars($item['title']) ?></p>
                                </div>
                            </a>
                        <?php endforeach;
                    }
                    ?>
                </div>
            </div>
        </aside>
    </div>
</main>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="recruit-pagination">
            <div class="pagination-wrapper">
                <?php if ($currentPage > 1): ?>
                    <a href="recruitment.php?page=<?= $currentPage - 1 ?>" class="page-btn">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == 1 || $i == $totalPages || ($i >= $currentPage - 2 && $i <= $currentPage + 2)): ?>
                        <a href="recruitment.php?page=<?= $i ?>" class="page-btn <?= $i == $currentPage ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php elseif ($i == $currentPage - 3 || $i == $currentPage + 3): ?>
                        <span class="page-btn" style="cursor: default;">...</span>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($currentPage < $totalPages): ?>
                    <a href="recruitment.php?page=<?= $currentPage + 1 ?>" class="page-btn">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php 
// Đóng kết nối database
if (isset($conn)) {
    closeDB($conn);
}
include 'includes/footer.php'; 
?>
