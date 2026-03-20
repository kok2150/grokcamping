<?php
session_start();
require_once '../includes/db_connect.php'; 

$is_logged_in = isset($_SESSION['user_id']);

$category = $_GET['category'] ?? '';
$brand = $_GET['brand'] ?? '';
$search = $_GET['search'] ?? '';

$query = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($category) { $query .= " AND category = ?"; $params[] = $category; }
if ($brand) { $query .= " AND brand = ?"; $params[] = $brand; }
if ($search) { $query .= " AND name LIKE ?"; $params[] = "%$search%"; }

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$all_cats = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
$all_brands = $pdo->query("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - GROK CAMPING</title>
    <style>
        :root {
            --bg: #000000;
            --bg-dark: #0a0a0a;
            --text: #e0e0e0;
            --text-muted: #a0a0a0;
            --accent: #daf63b;
            --border: #222222;
            --card: rgba(20, 20, 20, 0.9);
            --glow: rgba(218, 246, 59, 0.25);
        }

        html { overflow-y: scroll; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { 
            font-family: 'Helvetica Neue', Arial, sans-serif; 
            background: var(--bg); color: var(--text); 
            padding-top: 75px; 
        }

        /* --- NAVIGATION --- */
        nav {
            position: fixed; top: 0; left: 0; right: 0;
            background: rgba(0,0,0,0.92); backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border); z-index: 1000;
            padding: 0 5%; height: 75px; display: flex;
            align-items: center; justify-content: space-between;
        }
        .nav-brand { font-size: 1.5rem; font-weight: 700; letter-spacing: 2px; color: var(--text); text-decoration: none;}
        .nav-menu { display: flex; gap: 40px; }
        .nav-menu a { 
            color: var(--text-muted); 
            text-decoration: none; 
            font-size: 0.95rem; 
            font-weight: 700;
            padding-top: 15px; /* 字体往下移动 */
            display: inline-block;
        }
        .nav-menu a:hover, .nav-menu a.active { color: var(--accent); }
        .nav-right { display: flex; gap: 20px; align-items: center; }
        .nav-icon { width: 22px; height: 22px; object-fit: contain; }

        /* --- SHOP LAYOUT --- */
        .shop-container {
            max-width: 1400px; margin: 0 auto;
            display: grid; grid-template-columns: 240px 1fr;
            gap: 50px; padding: 40px 5%;
        }

        /* --- SIDEBAR --- */
        .sidebar h3 {
            font-size: 0.75rem; color: var(--accent); text-transform: uppercase;
            letter-spacing: 2px; margin-bottom: 20px; border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
        }
        .filter-list { list-style: none; margin-bottom: 40px; }
        .filter-list li { margin-bottom: 8px; }
        .filter-list a { 
            display: block; color: var(--text-muted); text-decoration: none; 
            font-size: 0.9rem; transition: all 0.2s ease;
            border-left: 2px solid transparent; padding-left: 10px;
        }
        .filter-list a:hover { color: #fff; border-left-color: #444; }
        .filter-list a.active { color: var(--accent); border-left-color: var(--accent); font-weight: 600; }

        /* --- PRODUCT CARD --- */
        .product-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }
        .product-card {
            position: relative; background: var(--bg-dark); border: 1px solid var(--border);
            border-radius: 24px; padding: 20px; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex; flex-direction: column;
        }
        .product-card:hover { border-color: var(--accent); transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.6); }

        .wishlist-btn {
            position: absolute; top: 15px; right: 15px; width: 48px; height: 48px; 
            background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 50%;
            display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10; transition: 0.3s;
        }
        .wishlist-btn svg { width: 24px; height: 24px; fill: none; stroke: white; stroke-width: 2; transition: 0.3s; }
        .wishlist-btn:hover { background: var(--accent); border-color: var(--accent); transform: scale(1.1); }
        .wishlist-btn:hover svg { stroke: #000; }

        .img-container {
            width: 100%; height: 240px; background: #111; border-radius: 18px; 
            margin-bottom: 20px; display: flex; align-items: center; justify-content: center; overflow: hidden;
        }
        .img-container img { max-width: 80%; height: auto; transition: 0.5s; }
        .product-card:hover .img-container img { transform: scale(1.1); }

        .p-info { flex-grow: 1; margin-bottom: 20px; }
        .p-title { font-size: 1.2rem; font-weight: 700; color: #fff; margin-bottom: 8px; line-height: 1.3; }
        .p-meta { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: flex; gap: 6px; margin-bottom: 12px; }
        .meta-tag { background: rgba(255,255,255,0.04); padding: 3px 10px; border-radius: 6px; border: 1px solid var(--border); text-transform: uppercase; }
        .p-price { font-size: 1.4rem; font-weight: 800; color: var(--accent); }

        .card-actions { display: flex; gap: 12px; }
        .btn-learn {
            flex: 1; text-align: center; text-decoration: none; padding: 12px 0; border: 1px solid #333;
            background: rgba(255, 255, 255, 0.05); color: #fff; border-radius: 50px; font-size: 0.75rem;
            font-weight: 700; transition: 0.3s; text-transform: uppercase;
        }
        .btn-learn:hover { background: #fff; color: #000; border-color: #fff; }

        .btn-cart {
            flex: 1.4; padding: 12px 0; background: var(--accent); color: #000; border: none; 
            border-radius: 50px; font-weight: 800; font-size: 0.75rem; cursor: pointer; transition: 0.3s; text-transform: uppercase;
        }
        .btn-cart:hover { background: #fff; transform: scale(1.03); box-shadow: 0 0 20px var(--glow); }

        @media (max-width: 850px) {
            .shop-container { grid-template-columns: 1fr; }
            .sidebar { display: none; }
        }
    </style>
</head>
<body>

    <nav>
        <a href="../index.php" class="nav-brand">GROK CAMPING</a>
        <div class="nav-menu">
            <a href="../index.php">HOME</a>
            <a href="products.php" class="active">PRODUCT</a>
            <a href="contact.php">CONTACT US</a>
        </div>
        
        <div class="nav-right">
            <form action="products.php" method="GET" style="display:flex; background:rgba(255,255,255,0.05); border-radius:20px; padding:5px 15px; border:1px solid var(--border);">
                <input type="text" name="search" placeholder="Search gear..." value="<?= htmlspecialchars($search) ?>" style="background:none; border:none; color:white; outline:none; width:100px;">
                <button type="submit" style="background:none; border:none; cursor:pointer;">
                    <img src="../assets/images/search-icon.png" class="nav-icon">
                </button>
            </form>
            <a href="wishlist.php" class="icon-link"><img src="../assets/images/wishlist-icon.png" class="nav-icon"></a>
            <a href="cart.php" class="icon-link"><img src="../assets/images/cart-icon.png" class="nav-icon"></a>
            <a href="<?= $is_logged_in ? 'profile.php' : 'login.php' ?>" class="icon-link"><img src="../assets/images/user-icon.png" class="nav-icon"></a>
        </div>
    </nav>

    <div class="shop-container">
        <aside class="sidebar">
            <h3>By Categories</h3>
            <ul class="filter-list">
                <li><a href="products.php" class="<?= !$category && !$brand ? 'active' : '' ?>">All Collection</a></li>
                <?php foreach($all_cats as $cat): ?>
                    <li><a href="?category=<?= urlencode($cat) ?>" class="<?= $category == $cat ? 'active' : '' ?>"><?= htmlspecialchars($cat) ?></a></li>
                <?php endforeach; ?>
            </ul>

            <h3>By Brands</h3>
            <ul class="filter-list">
                <?php foreach($all_brands as $b): ?>
                    <li><a href="?brand=<?= urlencode($b) ?>" class="<?= $brand == $b ? 'active' : '' ?>"><?= htmlspecialchars($b) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <main>
            <h2 style="margin-bottom: 30px; letter-spacing: 2px; text-transform: uppercase;">
                <?= htmlspecialchars($category ?: ($brand ?: ($search ? "Search: $search" : 'Exploration Gear'))) ?>
            </h2>

            <div class="product-grid">
                <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <div class="wishlist-btn" onclick="addToWishlist(<?= $p['id'] ?>)">
                        <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    </div>

                    <div class="img-container">
                        <img src="<?= htmlspecialchars($p['image_url'] ?: '../assets/images/placeholder.png') ?>" alt="Product">
                    </div>
                    
                    <div class="p-info">
                        <div class="p-title"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="p-meta">
                            <span class="meta-tag"><?= htmlspecialchars($p['brand']) ?></span>
                            <span class="meta-tag"><?= htmlspecialchars($p['category']) ?></span>
                        </div>
                        <div class="p-price">$<?= number_format($p['price'], 2) ?></div>
                    </div>
                    
                    <div class="card-actions">
                        <a href="product_details.php?id=<?= $p['id'] ?>" class="btn-learn">Learn More</a>
                        <button class="btn-cart" onclick="addToCart(<?= $p['id'] ?>)">Add to Cart</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($products)): ?>
                <div style="text-align: center; padding: 100px 0; color: var(--text-muted);">No gear found.</div>
            <?php endif; ?>
        </main>
    </div>

    <script>
    // 加入购物车功能
    function addToCart(productId) {
        fetch(`add_to_cart.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                alert('🛒 ' + data.message);
            } else if(data.message === 'Please login first') {
                window.location.href = 'login.php';
            } else {
                alert(data.message);
            }
        })
        .catch(err => console.error('Error:', err));
    }

    // 加入收藏夹功能
    function addToWishlist(productId) {
        if (event) event.stopPropagation();
        
        fetch(`add_to_wishlist.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                alert('❤️ ' + data.message);
            } else if(data.message === 'Please login first') {
                window.location.href = 'login.php';
            } else {
                alert(data.message);
            }
        })
        .catch(err => console.error('Error:', err));
    }
    </script>
</body>
</html>