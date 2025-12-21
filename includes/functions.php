<?php
/**
 * File chứa các hàm helper cho danh mục và sản phẩm
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Lấy tất cả danh mục cha (parent_id = NULL)
 */
function getParentCategories($conn) {
    $sql = "SELECT * FROM categories WHERE parent_id IS NULL AND status = 'active' ORDER BY name ASC";
    $result = $conn->query($sql);
    $categories = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    return $categories;
}

/**
 * Lấy tất cả danh mục con của một danh mục cha
 */
function getChildCategories($conn, $parent_id) {
    $sql = "SELECT * FROM categories WHERE parent_id = ? AND status = 'active' ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    $stmt->close();
    return $categories;
}

/**
 * Lấy tất cả danh mục (cả cha và con) được nhóm theo parent
 */
function getAllCategoriesGrouped($conn) {
    $parents = getParentCategories($conn);
    $result = [];
    foreach ($parents as $parent) {
        $children = getChildCategories($conn, $parent['id']);
        $parent['children'] = $children;
        $result[] = $parent;
    }
    return $result;
}

/**
 * Lấy thông tin danh mục theo ID
 */
function getCategoryById($conn, $category_id) {
    $sql = "SELECT * FROM categories WHERE id = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = null;
    if ($result && $result->num_rows > 0) {
        $category = $result->fetch_assoc();
    }
    $stmt->close();
    return $category;
}

/**
 * Lấy thông tin danh mục theo slug
 */
function getCategoryBySlug($conn, $slug) {
    $sql = "SELECT * FROM categories WHERE slug = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = null;
    if ($result && $result->num_rows > 0) {
        $category = $result->fetch_assoc();
    }
    $stmt->close();
    return $category;
}

/**
 * Lấy tất cả ID danh mục con (bao gồm cả chính nó nếu là danh mục cha)
 */
function getCategoryIdsIncludingChildren($conn, $category_id) {
    $category = getCategoryById($conn, $category_id);
    if (!$category) {
        return [$category_id];
    }
    
    $ids = [$category_id];
    
    // Nếu là danh mục cha, lấy tất cả ID con
    if ($category['parent_id'] === null) {
        $children = getChildCategories($conn, $category_id);
        foreach ($children as $child) {
            $ids[] = $child['id'];
        }
    }
    
    return $ids;
}

/**
 * Lấy danh sách sản phẩm với filter theo danh mục
 * Sử dụng category (varchar) để match với name hoặc slug của categories
 */
function getProducts($conn, $options = []) {
    $category_id = $options['category_id'] ?? null;
    $category_slug = $options['category_slug'] ?? null;
    $search = $options['search'] ?? '';
    $price_min = $options['price_min'] ?? null;
    $price_max = $options['price_max'] ?? null;
    $brand_id = $options['brand_id'] ?? null;
    $size_id = $options['size_id'] ?? null;
    $sort = $options['sort'] ?? 'latest';
    $page = $options['page'] ?? 1;
    $perPage = $options['per_page'] ?? 16;
    $offset = ($page - 1) * $perPage;
    
    $where = ["p.status = 'active'"];
    $params = [];
    $types = '';
    
    // Lọc theo danh mục
    if ($category_id) {
        $category = getCategoryById($conn, $category_id);
        if ($category) {
            // Tạo mảng tên danh mục cần tìm (bao gồm danh mục cha và tất cả danh mục con)
            $categoryNames = [$category['name']];
            
            // Nếu là danh mục cha, lấy tên tất cả danh mục con
            if ($category['parent_id'] === null) {
                $children = getChildCategories($conn, $category_id);
                foreach ($children as $child) {
                    $categoryNames[] = $child['name'];
                }
            }
            
            // Tạo placeholders cho IN clause
            $placeholders = str_repeat('?,', count($categoryNames) - 1) . '?';
            $where[] = "p.category IN ($placeholders)";
            foreach ($categoryNames as $name) {
                $params[] = $name;
                $types .= 's';
            }
        }
    } elseif ($category_slug) {
        $category = getCategoryBySlug($conn, $category_slug);
        if ($category) {
            $categoryNames = [$category['name']];
            if ($category['parent_id'] === null) {
                $children = getChildCategories($conn, $category['id']);
                foreach ($children as $child) {
                    $categoryNames[] = $child['name'];
                }
            }
            $placeholders = str_repeat('?,', count($categoryNames) - 1) . '?';
            $where[] = "p.category IN ($placeholders)";
            foreach ($categoryNames as $name) {
                $params[] = $name;
                $types .= 's';
            }
        }
    }
    
    // Lọc theo giá
    if ($price_min !== null && $price_max !== null) {
        // Lọc theo giá bán (sale_price nếu có, không thì price)
        $where[] = "COALESCE(p.sale_price, p.price) >= ? AND COALESCE(p.sale_price, p.price) <= ?";
        $params[] = $price_min;
        $params[] = $price_max;
        $types .= 'dd';
    } elseif ($price_min !== null) {
        $where[] = "COALESCE(p.sale_price, p.price) >= ?";
        $params[] = $price_min;
        $types .= 'd';
    } elseif ($price_max !== null) {
        $where[] = "COALESCE(p.sale_price, p.price) <= ?";
        $params[] = $price_max;
        $types .= 'd';
    }
    
    // Tìm kiếm
    if (!empty($search)) {
        $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }
    
    // Lọc theo thương hiệu
    if ($brand_id !== null) {
        $where[] = "p.brand_id = ?";
        $params[] = $brand_id;
        $types .= 'i';
    }
    
    // Lọc theo kích thước
    if ($size_id !== null) {
        $where[] = "p.size_id = ?";
        $params[] = $size_id;
        $types .= 'i';
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Sắp xếp và JOIN cần thiết
    $joinClause = "";
    $orderBy = "p.created_at DESC";
    $groupBy = "";
    
    switch ($sort) {
        case 'price_asc':
            $orderBy = "COALESCE(p.sale_price, p.price) ASC";
            break;
        case 'price_desc':
            $orderBy = "COALESCE(p.sale_price, p.price) DESC";
            break;
        case 'name_asc':
            $orderBy = "p.name ASC";
            break;
        case 'name_desc':
            $orderBy = "p.name DESC";
            break;
        case 'popular':
            // Sắp xếp theo số lượng bán (tổng quantity từ order_items)
            $joinClause = "LEFT JOIN order_items oi ON p.id = oi.product_id 
                          LEFT JOIN orders o ON oi.order_id = o.id AND o.status IN ('processing', 'shipped', 'delivered')";
            $orderBy = "COALESCE(SUM(oi.quantity), 0) DESC";
            $groupBy = "GROUP BY p.id";
            break;
        case 'rating':
            // Sắp xếp theo xếp hạng trung bình từ reviews
            $joinClause = "LEFT JOIN reviews r ON p.id = r.product_id AND r.status = 'approved'";
            $orderBy = "COALESCE(AVG(r.rating), 0) DESC, COUNT(r.id) DESC";
            $groupBy = "GROUP BY p.id";
            break;
        case 'latest':
            $orderBy = "p.created_at DESC";
            break;
        case 'default':
        default:
            $orderBy = "p.id DESC";
            break;
    }
    
    // Đếm tổng số sản phẩm
    $countSql = "SELECT COUNT(DISTINCT p.id) as total 
                 FROM products p 
                 $joinClause
                 WHERE $whereClause";
    $countStmt = $conn->prepare($countSql);
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $total = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($total / $perPage);
    $countStmt->close();
    
    // Lấy danh sách sản phẩm
    $sql = "SELECT p.*";
    
    // Thêm các trường tính toán nếu cần
    if ($sort === 'popular') {
        $sql .= ", COALESCE(SUM(oi.quantity), 0) as total_sold";
    } elseif ($sort === 'rating') {
        $sql .= ", COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count";
    }
    
    $sql .= " FROM products p 
              $joinClause
              WHERE $whereClause";
    
    if (!empty($groupBy)) {
        $sql .= " $groupBy";
    }
    
    $sql .= " ORDER BY $orderBy
              LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $params[] = $perPage;
    $params[] = $offset;
    $types .= 'ii';
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    $stmt->close();
    
    return [
        'products' => $products,
        'total' => $total,
        'total_pages' => $totalPages,
        'current_page' => $page
    ];
}

/**
 * Lấy danh mục cha có sản phẩm (dùng cho trang chủ)
 * @param mysqli $conn Kết nối database
 * @param int|null $limit Giới hạn số danh mục (null = không giới hạn)
 * @param int $productsPerCategory Số sản phẩm mỗi danh mục (mặc định 6)
 * @return array Danh sách danh mục với sản phẩm
 */
function getParentCategoriesWithProducts($conn, $limit = null, $productsPerCategory = 6) {
    // Lấy tất cả danh mục cha
    $parentCategories = getParentCategories($conn);
    $categoriesWithProducts = [];
    
    foreach ($parentCategories as $category) {
        // Lấy danh mục con
        $children = getChildCategories($conn, $category['id']);
        $category['children'] = $children;
        
        // Tạo danh sách tên danh mục (bao gồm cả cha và con)
        $categoryNames = [$category['name']];
        foreach ($children as $child) {
            $categoryNames[] = $child['name'];
        }
        
        // Lấy sản phẩm từ tất cả danh mục (cha và con)
        if (!empty($categoryNames)) {
            $placeholders = str_repeat('?,', count($categoryNames) - 1) . '?';
            $productSql = "SELECT * FROM products 
                           WHERE category IN ($placeholders) 
                           AND status = 'active' 
                           ORDER BY created_at DESC 
                           LIMIT ?";
            $stmt = $conn->prepare($productSql);
            $types = str_repeat('s', count($categoryNames)) . 'i';
            $params = array_merge($categoryNames, [$productsPerCategory]);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $productResult = $stmt->get_result();
            $products = [];
            while ($prod = $productResult->fetch_assoc()) {
                $products[] = $prod;
            }
            $stmt->close();
            
            // Chỉ thêm danh mục vào danh sách nếu có sản phẩm
            if (!empty($products)) {
                $category['products'] = $products;
                $category['product_count'] = count($products);
                $categoriesWithProducts[] = $category;
            }
        }
        
        // Giới hạn số lượng danh mục nếu có
        if ($limit && count($categoriesWithProducts) >= $limit) {
            break;
        }
    }
    
    return $categoriesWithProducts;
}

/**
 * Lấy danh sách tin tức
 * @param mysqli $conn Kết nối database
 * @param array $options Tùy chọn: page, per_page, status, featured, category
 * @return array Danh sách tin tức với pagination
 */
function getNews($conn, $options = []) {
    $page = $options['page'] ?? 1;
    $perPage = $options['per_page'] ?? 9;
    $status = $options['status'] ?? 'published';
    $featured = $options['featured'] ?? null;
    $category = $options['category'] ?? null;
    $offset = ($page - 1) * $perPage;
    
    $where = [];
    $params = [];
    $types = '';
    
    if ($status) {
        $where[] = "status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    if ($featured !== null) {
        $where[] = "featured = ?";
        $params[] = $featured;
        $types .= 'i';
    }
    
    if ($category) {
        $where[] = "category = ?";
        $params[] = $category;
        $types .= 's';
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Đếm tổng số
    $countSql = "SELECT COUNT(*) as total FROM news $whereClause";
    $countStmt = $conn->prepare($countSql);
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $total = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($total / $perPage);
    $countStmt->close();
    
    // Lấy danh sách
    $sql = "SELECT * FROM news $whereClause ORDER BY published_at DESC, created_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $params[] = $perPage;
    $params[] = $offset;
    $types .= 'ii';
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $news = [];
    while ($row = $result->fetch_assoc()) {
        $news[] = $row;
    }
    $stmt->close();
    
    return [
        'news' => $news,
        'total' => $total,
        'total_pages' => $totalPages,
        'current_page' => $page
    ];
}

/**
 * Lấy tin tức theo slug
 */
function getNewsBySlug($conn, $slug) {
    $sql = "SELECT * FROM news WHERE slug = ? AND status = 'published'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    $news = null;
    if ($result && $result->num_rows > 0) {
        $news = $result->fetch_assoc();
        // Tăng lượt xem
        $updateSql = "UPDATE news SET views = views + 1 WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $news['id']);
        $updateStmt->execute();
        $updateStmt->close();
    }
    $stmt->close();
    return $news;
}

/**
 * Lấy danh sách tuyển dụng
 * @param mysqli $conn Kết nối database
 * @param array $options Tùy chọn: page, per_page, status
 * @return array Danh sách tuyển dụng với pagination
 */
function getRecruitment($conn, $options = []) {
    $page = $options['page'] ?? 1;
    $perPage = $options['per_page'] ?? 10;
    $status = $options['status'] ?? 'open';
    $offset = ($page - 1) * $perPage;
    
    $where = ["status = ?"];
    $params = [$status];
    $types = 's';
    
    $whereClause = 'WHERE ' . implode(' AND ', $where);
    
    // Đếm tổng số
    $countSql = "SELECT COUNT(*) as total FROM recruitment $whereClause";
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $total = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($total / $perPage);
    $countStmt->close();
    
    // Lấy danh sách
    $sql = "SELECT * FROM recruitment $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $params[] = $perPage;
    $params[] = $offset;
    $types .= 'ii';
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $recruitment = [];
    while ($row = $result->fetch_assoc()) {
        $recruitment[] = $row;
    }
    $stmt->close();
    
    return [
        'recruitment' => $recruitment,
        'total' => $total,
        'total_pages' => $totalPages,
        'current_page' => $page
    ];
}

/**
 * Lấy tuyển dụng theo slug
 */
function getRecruitmentBySlug($conn, $slug) {
    $sql = "SELECT * FROM recruitment WHERE slug = ? AND status = 'open'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    $recruitment = null;
    if ($result && $result->num_rows > 0) {
        $recruitment = $result->fetch_assoc();
        // Tăng lượt xem
        $updateSql = "UPDATE recruitment SET views = views + 1 WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $recruitment['id']);
        $updateStmt->execute();
        $updateStmt->close();
    }
    $stmt->close();
    return $recruitment;
}

/**
 * Lấy giỏ hàng từ session hoặc database
 * @param mysqli $conn Kết nối database
 * @return array Danh sách sản phẩm trong giỏ hàng
 */
function getCartItems($conn) {
    $cartItems = [];
    
    // Kiểm tra session cart
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            // Lấy thông tin sản phẩm từ database
            $productId = (int)$item['product_id'];
            $sql = "SELECT * FROM products WHERE id = ? AND status = 'active'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $product = $result->fetch_assoc();
                $cartItems[] = [
                    'id' => $product['id'],
                    'product_id' => $product['id'],
                    'name' => $product['name'],
                    'img' => $product['image'],
                    'price' => (float)($item['sale_price'] ?? $product['sale_price'] ?? $product['price']),
                    'quantity' => (int)($item['quantity'] ?? 1),
                    'weight_option' => $item['weight_option'] ?? null
                ];
            }
            $stmt->close();
        }
    }
    
    return $cartItems;
}

/**
 * Tính tổng tiền giỏ hàng
 */
function calculateCartTotal($cartItems) {
    $subtotal = 0;
    foreach ($cartItems as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    return $subtotal;
}

/**
 * Lấy danh sách danh mục tin tức (để hiển thị sidebar)
 */
function getNewsCategories($conn) {
    $sql = "SELECT DISTINCT category FROM news WHERE status = 'published' AND category IS NOT NULL ORDER BY category ASC";
    $result = $conn->query($sql);
    $categories = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
    }
    return $categories;
}

?>

