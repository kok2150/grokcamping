<?php
session_start();
// 1. 引入配置文件和数据库连接
require_once '../includes/config.php'; // 确保路径正确指向 config.php
require_once '../includes/db_connect.php'; 

$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - GROK CAMPING</title>
    <style>
        :root {
            --bg: #000000;
            --bg-dark: #0a0a0a;
            --text: #e0e0e0;
            --text-muted: #a0a0a0;
            --accent: #daf63b;
            --border: #222222;
            --card: rgba(20, 20, 20, 0.9);
            --glow: rgba(218, 246, 59, 0.2);
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body { 
            font-family: 'Helvetica Neue', Arial, sans-serif; 
            background: var(--bg); color: var(--text); 
            padding-top: 75px; /* 必须与 nav 高度一致 */
            overflow-x: hidden;
        }

        /* ============================================================
           --- NAVIGATION (100% 像素级同步自 index.php，使用绝对路径) ---
           ============================================================ */
        nav {
            position: fixed; top: 0; left: 0; right: 0;
            background: rgba(0,0,0,0.92); backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border); z-index: 1000;
            padding: 0 5%; height: 75px; /* 固定的高度 */
            display: flex; align-items: center; justify-content: space-between;
        }
        .nav-brand { font-size: 1.5rem; font-weight: 700; letter-spacing: 2px; color: var(--text); text-decoration: none;}
        .nav-menu { display: flex; gap: 40px; }
        .nav-menu a { 
            color: var(--text-muted); 
            text-decoration: none; 
            font-size: 0.95rem; 
            font-weight: 700; 
            transition: color 0.2s; 
            display: inline-block;
            padding: 5px 0; /* 增加固定内边距，防止字号微调导致的跳动 */
        }
        .nav-menu a:hover, .nav-menu a.active { color: var(--accent); }
        .nav-right { display: flex; gap: 25px; align-items: center; }

        /* 搜索框样式完全同步 index */
        .search-container {
            display: flex; align-items: center;
            background: rgba(255,255,255,0.05); /* 确保与 index 一致 */
            border: 1px solid var(--border);
            border-radius: 20px; padding: 5px 15px;
        }
        .search-input {
            background: transparent; border: none; color: white;
            font-size: 0.85rem; outline: none; width: 100px;
        }
        .search-btn { background: none; border: none; cursor: pointer; display: flex; align-items: center; padding: 0; }

        .nav-icon { width: 22px; height: 22px; object-fit: contain; display: block; }
        .icon-link { display: flex; align-items: center; justify-content: center; transition: transform 0.2s; text-decoration: none; }
        .icon-link:hover { transform: scale(1.1); }

        @media (max-width: 768px) {
            nav .nav-menu { display: none; } /* 同步 index 的隐藏逻辑 */
        }

        /* ============================================================
           --- CONTACT CONTENT (保持原有硬核风格) ---
           ============================================================ */
        .contact-container {
            max-width: 1200px; margin: 80px auto;
            padding: 0 5%; display: grid;
            grid-template-columns: 1fr 1.2fr; gap: 80px;
            align-items: start;
        }

        .contact-header { position: sticky; top: 150px; }
        .contact-header h1 {
            font-size: 4rem; font-weight: 900; line-height: 1;
            text-transform: uppercase; letter-spacing: -2px;
            margin-bottom: 25px;
        }
        .contact-header p {
            color: var(--text-muted); font-size: 1.1rem; max-width: 400px;
            line-height: 1.6; border-left: 2px solid var(--accent);
            padding-left: 20px;
        }

        .info-grid { display: flex; flex-direction: column; gap: 15px; }
        .info-card {
            background: var(--bg-dark); border: 1px solid var(--border);
            padding: 25px 30px; border-radius: 24px; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none; color: inherit;
        }
        .info-card:hover {
            border-color: var(--accent); transform: translateX(10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            background: rgba(255,255,255,0.02);
        }

        .info-label {
            font-size: 0.7rem; color: var(--accent);
            text-transform: uppercase; letter-spacing: 2px;
            margin-bottom: 10px; font-weight: 700;
        }
        .info-value { font-size: 1.25rem; font-weight: 600; line-height: 1.5; }

        .glow-circle {
            position: fixed; width: 400px; height: 400px;
            background: var(--glow); filter: blur(100px);
            border-radius: 50%; z-index: -1; bottom: -100px; right: -100px;
            pointer-events: none;
        }

        @media (max-width: 900px) {
            .contact-container { grid-template-columns: 1fr; text-align: center; margin: 40px auto; }
            .contact-header { position: static; margin-bottom: 40px; }
            .contact-header p { margin: 0 auto; text-align: left; }
            .info-card:hover { transform: translateY(-5px); }
        }
    </style>
</head>
<body>

    <nav>
        <a href="<?= BASE_URL ?>index.php" class="nav-brand">GROK CAMPING</a>
        <div class="nav-menu">
            <a href="<?= BASE_URL ?>index.php">HOME</a>
            <a href="<?= BASE_URL ?>pages/products.php">PRODUCT</a>
            <a href="<?= BASE_URL ?>pages/contact.php" class="active">CONTACT US</a>
        </div>
        
        <div class="nav-right">
            <form action="<?= BASE_URL ?>pages/products.php" method="GET" class="search-container">
                <input type="text" name="search" class="search-input" placeholder="Search gear...">
                <button type="submit" class="search-btn">
                    <img src="<?= BASE_URL ?>assets/images/search-icon.png" alt="Search" class="nav-icon">
                </button>
            </form>

            <a href="<?= BASE_URL ?>pages/wishlist.php" class="icon-link" title="Wishlist">
                <img src="<?= BASE_URL ?>assets/images/wishlist-icon.png" alt="Wishlist" class="nav-icon">
            </a>

            <a href="<?= BASE_URL ?>pages/cart.php" class="icon-link" title="Cart">
                <img src="<?= BASE_URL ?>assets/images/cart-icon.png" alt="Cart" class="nav-icon">
            </a>

            <a href="<?= $is_logged_in ? BASE_URL.'pages/profile.php' : BASE_URL.'pages/login.php' ?>" class="icon-link" title="Account">
                <img src="<?= BASE_URL ?>assets/images/user-icon.png" alt="Account" class="nav-icon">
            </a>
        </div>
    </nav>

    <div class="glow-circle"></div>

    <div class="contact-container">
        <div class="contact-header">
            <h1>Reach<br><span style="color: var(--accent);">Grok</span></h1>
            <p>Gear up for your next adventure. If you have questions about our equipment or location, reach out to our team.</p>
        </div>

        <div class="info-grid">
            <a href="mailto:grokcamping@gmail.com" class="info-card">
                <div class="info-label">Email Support</div>
                <div class="info-value">grokcamping@gmail.com</div>
            </a>

            <a href="tel:+601135894636" class="info-card">
                <div class="info-label">Contact Line</div>
                <div class="info-value">+60 11 3589 4636</div>
            </a>

            <div class="info-card">
                <div class="info-label">Operation Hours</div>
                <div class="info-value">
                    Mon - Sat: 9:00AM - 6:00PM<br>
                    <span style="color: #7e7e7e; opacity: 0.9;">Sun: Closed</span>
                </div>
            </div>

            <div class="info-card">
                <div class="info-label">Location HQ</div>
                <div class="info-value">
                    16, Jalan MJ 17, Taman Industri Meranti Jaya,<br>
                    Puchong, 47120, Selangor, Malaysia.
                </div>
            </div>
        </div>
    </div>

</body>
</html>