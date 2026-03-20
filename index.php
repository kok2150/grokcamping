<?php
session_start();
// 1. 引入数据库连接
require_once 'includes/db_connect.php'; 

$page_title = "Grok Gaming - Logitech Gaming Gear";
$is_logged_in = isset($_SESSION['user_id']);

// 2. 从数据库抓取推荐产品
try {
    $stmt = $pdo->query("SELECT * FROM products WHERE is_featured = 1 LIMIT 4");
    $db_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $js_products = !empty($db_products) ? json_encode($db_products) : 'null';
} catch (PDOException $e) {
    $js_products = 'null';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        :root {
            --bg: #000000;
            --bg-dark: #0a0a0a;
            --text: #e0e0e0;
            --text-muted: #a0a0a0;
            --accent: #daf63b; 
            --accent-dark: #ebe825;
            --border: #222222;
            --card: rgba(20, 20, 20, 0.9);
            --overlay: rgba(0,0,0,0.65);
            --glow: rgba(218, 246, 59, 0.25);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { 
            font-family: 'Helvetica Neue', 'Arial', sans-serif; 
            background: var(--bg); 
            color: var(--text); 
            line-height: 1.5; 
            overflow-x: hidden;
        }

        /* --- NAVIGATION (同步自 Product.php) --- */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            background: rgba(0,0,0,0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            z-index: 1000;
            padding: 0 5%;
            height: 75px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .nav-brand { font-size: 1.5rem; font-weight: 700; letter-spacing: 2px; color: var(--text); text-decoration: none;}
        
        .nav-menu { display: flex; gap: 40px; }
        .nav-menu a { 
            color: var(--text-muted); 
            text-decoration: none; 
            font-size: 0.95rem; 
            font-weight: 700; 
            transition: color 0.2s; 
            padding-top: 2px;
        }
        .nav-menu a:hover, .nav-menu a.active { color: var(--accent); }
        
        .nav-right { display: flex; gap: 20px; align-items: center; }
        
        /* 搜索框同步 */
        .search-container {
            display: flex;
            align-items: center;
            background: rgba(255,255,255,0.05); /* 降低了透明度以同步 */
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 5px 15px; /* 同步 padding */
        }
        .search-input {
            background: transparent;
            border: none;
            color: white;
            font-size: 0.85rem;
            outline: none;
            width: 100px; /* 初始宽度同步 */
        }
        .search-btn { background: none; border: none; cursor: pointer; display: flex; align-items: center; padding: 0; }

        .nav-icon {
            width: 22px; 
            height: 22px; 
            object-fit: contain;
        }
        .icon-link { display: flex; align-items: center; justify-content: center; transition: transform 0.2s; }
        .icon-link:hover { transform: scale(1.1); }

        /* HERO SECTION */
        .hero {
            position: relative;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            text-align: left;
            overflow: hidden;
        }
        .hero-bg-video, .hero-bg-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
        }
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.9) 85%);
            z-index: 2;
        }
        .hero-main-content {
            position: relative;
            z-index: 3;
            padding: 0 10% 220px 10%;
            max-width: 950px;
        }
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 0.2rem;
        }
        .hero .subtitle {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 1.2rem;
            text-transform: uppercase;
        }
        .hero p {
            font-size: 1.1rem;
            color: var(--text-muted);
            max-width: 600px;
            margin-bottom: 2rem;
        }
        .btn-primary {
            padding: 14px 45px;
            background: transparent;
            color: white;
            border: 1px solid white;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary:hover {
            background: white;
            color: black;
            transform: translateY(-3px);
        }

        /* CAROUSEL CARDS */
        .featured-products {
            position: relative;
            z-index: 4;
            margin-top: -150px;
            padding: 0 5% 80px;
        }
        .carousel-grid {
            display: flex;
            gap: 15px;
            justify-content: center;
            max-width: 1400px;
            margin: 0 auto;
            overflow-x: auto;
            padding: 10px;
        }
        .carousel-card {
            background: #111;
            border: 2px solid transparent;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.4s ease;
            min-width: 220px;
            width: 220px;
            height: 130px;
            position: relative;
            flex-shrink: 0;
        }
        .carousel-card.active {
            border-color: var(--accent);
            box-shadow: 0 0 20px var(--glow);
        }
        .carousel-card img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 10px;
            filter: brightness(0.7);
        }
        .carousel-info {
            position: absolute;
            bottom: 10px;
            left: 12px;
            z-index: 2;
        }
        .carousel-name { font-size: 0.85rem; font-weight: 800; text-shadow: 0 2px 4px black; }

        /* CATEGORIES */
        .categories {
            padding: 100px 5%;
            background: var(--bg-dark);
            text-align: center;
        }
        .categories h2 {
            font-size: 2.2rem;
            margin-bottom: 50px;
            letter-spacing: 2px;
        }
        .cat-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        .cat-card {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            aspect-ratio: 1 / 1; 
            background: #151515;
            border: 1px solid var(--border);
            transition: all 0.4s ease;
            text-decoration: none;
        }
        .cat-card:hover {
            transform: translateY(-10px);
            border-color: var(--accent);
        }
        .cat-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.8);
        }
        .cat-label {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            padding: 25px 10px 15px;
            background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, transparent 100%);
            color: #ffffff;
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
        }
        
        footer {
            background: #000;
            padding: 60px 5%;
            text-align: center;
            color: var(--text-muted);
            border-top: 1px solid var(--border);
        }

        @media (max-width: 768px) {
            .nav-menu { display: none; }
        }
    </style>
</head>
<body>

    <nav>
        <a href="index.php" class="nav-brand">GROK CAMPING</a>
        <div class="nav-menu">
            <a href="index.php" class="active">HOME</a>
            <a href="pages/products.php">PRODUCT</a>
            <a href="pages/contact.php">CONTACT US</a>
        </div>
        
        <div class="nav-right">
            <form action="pages/products.php" method="GET" class="search-container">
                <input type="text" name="search" class="search-input" placeholder="Search gear...">
                <button type="submit" class="search-btn">
                    <img src="assets/images/search-icon.png" alt="Search" class="nav-icon">
                </button>
            </form>

            <a href="pages/wishlist.php" class="icon-link" title="Wishlist">
                <img src="assets/images/wishlist-icon.png" alt="Wishlist" class="nav-icon">
            </a>

            <a href="pages/cart.php" class="icon-link" title="Cart">
                <img src="assets/images/cart-icon.png" alt="Cart" class="nav-icon">
            </a>

            <a href="<?= $is_logged_in ? 'pages/profile.php' : 'pages/login.php' ?>" class="icon-link" title="Account">
                <img src="assets/images/user-icon.png" alt="Account" class="nav-icon">
            </a>
        </div>
    </nav>

    <section class="hero" id="hero">
        <div class="hero-overlay"></div>
        <div class="hero-main-content">
            <h1 id="hero-title">LOADING...</h1>
            <div class="subtitle" id="hero-subtitle"></div>
            <p id="hero-desc"></p>
            <a href="pages/products.php" class="btn-primary" id="hero-btn">LEARN MORE</a>
        </div>

        <div class="featured-products">
            <div class="carousel-grid">
                <div class="carousel-card active" data-index="0">
                    <img src="assets/images/pro-x-superlight-2.png" alt="Mice">
                    <div class="carousel-info"><div class="carousel-name">PRO X2</div></div>
                </div>
                <div class="carousel-card" data-index="1">
                    <img src="assets/images/Yeti1500x5.webp" alt="Audio">
                    <div class="carousel-info"><div class="carousel-name">1500x5</div></div>
                </div>
                <div class="carousel-card" data-index="2">
                    <img src="assets/images/racing-wheels.webp" alt="Sim">
                    <div class="carousel-info"><div class="carousel-name">SIM RACING</div></div>
                </div>
                <div class="carousel-card" data-index="3">
                    <img src="assets/images/g515-rapid-tkl.png" alt="Keyboard">
                    <div class="carousel-info"><div class="carousel-name">G515 TKL</div></div>
                </div>
            </div>
        </div>
    </section>

    <section class="categories">
        <h2>BY CATEGORY</h2>
        <div class="cat-grid">
            <a href="pages/products.php?category=Mice" class="cat-card">
                <img src="assets/images/mice.webp" alt="Gaming Mice">
                <div class="cat-label">GAMING MICE</div>
            </a>
            <a href="pages/products.php?category=Keyboards" class="cat-card">
                <img src="assets/images/keyboard.webp" alt="Gaming Keyboards">
                <div class="cat-label">GAMING KEYBOARDS</div>
            </a>
            <a href="pages/products.php?category=Headsets" class="cat-card">
                <img src="assets/images/headsets.webp" alt="Headsets">
                <div class="cat-label">HEADSETS & AUDIO</div>
            </a>
            <a href="pages/products.php?category=Mousepads" class="cat-card">
                <img src="assets/images/mousepad.webp" alt="Mousepads">
                <div class="cat-label">MOUSEPADS</div>
            </a>
            <a href="pages/products.php?category=Microphones" class="cat-card">
                <img src="assets/images/microphones.webp" alt="Microphones">
                <div class="cat-label">MICROPHONES</div>
            </a>
        </div>
    </section>

    <footer>
        <p>© 2026 Grok Gaming • Authorized Logitech G Partner • All Rights Reserved</p>
    </footer>

    <script>
    let productsFromDB = <?php echo $js_products; ?>;
    
    const defaultProducts = [
        { type: 'video', src: 'https://www.youtube.com/embed/9cVWD6-BMmA?autoplay=1&mute=1&loop=1&playlist=9cVWD6-BMmA&controls=0&showinfo=0&rel=0&modestbranding=1&iv_load_policy=3',
          title: 'PRO X2 SUPERSTRIKE', subtitle: 'THE FASTEST CLICK',
          desc: 'Revolutionary Haptic Inductive Trigger System. Tunable actuation.',
          btn: 'LEARN MORE' },
        { type: 'local_video', src: 'assets/videos/goalzero.mp4',                
          title: 'Goal Zero 1500X', subtitle: 'PORTABLE POWER',
          desc: 'The Goal Zero 1500x comes with impressive power output for its size!',
          btn: 'SHOP NOW' },
        { type: 'image', src: 'assets/images/racing-wheels.webp',
          title: 'SIM RACING SEATS', subtitle: 'ULTIMATE IMMERSION',
          desc: 'Playseat Challenge X or Trophy Logitech G Edition racing seats.',
          btn: 'EXPLORE SEATS' },
        { type: 'image', src: 'assets/images/g515-rapid-tkl.png',
          title: 'G515 RAPID TKL', subtitle: 'LOW-PROFILE PRECISION',
          desc: 'Slim wired gaming keyboard with fully customizable magnetic switches.',
          btn: 'SHOP NOW' }
    ];

    const products = (productsFromDB && productsFromDB.length > 0) ? productsFromDB : defaultProducts;

    const heroContent = document.getElementById('hero');
    const heroTitle = document.getElementById('hero-title');
    const heroSubtitle = document.getElementById('hero-subtitle');
    const heroDesc = document.getElementById('hero-desc');
    const heroBtn = document.getElementById('hero-btn');
    const cards = document.querySelectorAll('.carousel-card');

    function switchProduct(index) {
        if (!products[index]) return;
        const prod = products[index];
        cards.forEach((c, i) => c.classList.toggle('active', i === index));

        const oldVideo = document.querySelector('.hero-bg-video');
        const oldImg = document.querySelector('.hero-bg-img');
        if (oldVideo) oldVideo.remove();
        if (oldImg) oldImg.remove();

        if (prod.type === 'video' || (prod.media_type === 'video')) {
            const iframe = document.createElement('iframe');
            iframe.className = 'hero-bg-video';
            iframe.src = prod.src || prod.media_src;
            iframe.allow = 'autoplay; encrypted-media';
            heroContent.prepend(iframe);
        } else if (prod.type === 'local_video' || (prod.media_type === 'local_video')) {
            const video = document.createElement('video');
            video.className = 'hero-bg-video';
            video.autoplay = true; video.muted = true; video.loop = true; video.playsInline = true;
            const source = document.createElement('source');
            source.src = prod.src || prod.media_src;
            source.type = 'video/mp4';
            video.appendChild(source);
            heroContent.prepend(video);
        } else {
            const img = document.createElement('img');
            img.className = 'hero-bg-img';
            img.src = prod.src || prod.media_src;
            img.alt = prod.title || prod.name;
            heroContent.prepend(img);
        }

        heroTitle.textContent = prod.title || prod.name;
        heroSubtitle.textContent = prod.subtitle;
        heroDesc.textContent = prod.desc || prod.description;
        heroBtn.textContent = prod.btn || "LEARN MORE";
    }

    cards.forEach(card => {
        card.addEventListener('click', () => {
            switchProduct(parseInt(card.dataset.index));
        });
    });

    switchProduct(0);
    </script>
</body>
</html>