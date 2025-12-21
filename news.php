<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = "Tin tức - Bách Hóa Xanh";

// Kết nối database
$conn = connectDB();

// Lấy trang hiện tại
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Lấy danh sách tin tức
$newsData = getNews($conn, ['page' => $page, 'per_page' => 9, 'status' => 'published']);
$newsArticles = $newsData['news'];
$totalPages = $newsData['total_pages'];
$currentPage = $newsData['current_page'];

include 'includes/header.php';
?>

<main class="news-page">
    <div class="container news-layout">
        <div class="news-content-wrapper">
            <?php foreach ($newsArticles as $article): 
                $imgPath = !empty($article['featured_image']) ? 'assets/images/' . $article['featured_image'] : '';
                $excerpt = !empty($article['excerpt']) ? $article['excerpt'] : (mb_substr(strip_tags($article['content']), 0, 100) . '...');
                ?>
                <a href="news-detail.php?slug=<?= htmlspecialchars($article['slug']) ?>" class="news-card">
                    <?php if (!empty($imgPath)): ?>
                    <div class="news-card-thumb">
                        <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                    </div>
                    <?php endif; ?>
                    <div class="news-card-body">
                        <h3 class="news-card-title"><?= htmlspecialchars($article['title']) ?></h3>
                        <p class="news-card-desc"><?= htmlspecialchars($excerpt) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <aside class="recruit-sidebar">
            <div class="recruit-widget">
                <h4>CHUYÊN MỤC TIN TỨC</h4>
                <ul class="recruit-cats">
                    <li><a href="#">Thời trang &amp; cuộc sống</a></li>
                    <li><a href="#">Tin công nghệ</a></li>
                    <li class="active"><a href="#">Tin tức</a></li>
                    <li><a href="#">Tuyển dụng</a></li>
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
                            $imgPath = !empty($item['featured_image']) ? 'assets/images/' . $item['featured_image'] : '';
                            ?>
                            <a href="news-detail.php?slug=<?= htmlspecialchars($item['slug']) ?>" class="latest-item">
                                <?php if (!empty($imgPath)): ?>
                                <div class="latest-thumb">
                                    <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                </div>
                                <?php endif; ?>
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
        
        <!-- Pagination - Căn giữa 3 cột tin tức -->
        <?php if ($totalPages > 1): ?>
        <div class="news-pagination-col">
            <div class="pagination-wrapper">
                <?php if ($currentPage > 1): ?>
                    <a href="news.php?page=<?= $currentPage - 1 ?>" class="page-btn">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == 1 || $i == $totalPages || ($i >= $currentPage - 2 && $i <= $currentPage + 2)): ?>
                        <a href="news.php?page=<?= $i ?>" class="page-btn <?= $i == $currentPage ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php elseif ($i == $currentPage - 3 || $i == $currentPage + 3): ?>
                        <span class="page-btn" style="cursor: default;">...</span>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($currentPage < $totalPages): ?>
                    <a href="news.php?page=<?= $currentPage + 1 ?>" class="page-btn">
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



