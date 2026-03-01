<?php
session_start();
// include 'includes/db_connect.php'; 

$page_title = "Grok Gaming - Logitech Gaming Gear";
$is_logged_in = isset($_SESSION['user_id']);
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
            --overlay: rgba(0, 0, 0, 0.65);
            --glow: rgba(59, 130, 246, 0.25);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { 
            font-family: 'Helvetica Neue', 'Arial', sans-serif; 
            background: var(--bg); 
            color: var(--text); 
            line-height: 1.5; 
            overflow-x: hidden;
        }

        /* NAVIGATION */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            background: rgba(0,0,0,0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            z-index: 1000;
            padding: 0 5%;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .nav-brand { font-size: 1.5rem; font-weight: 700; letter-spacing: 2px; color: var(--text); }
        .nav-menu { display: flex; gap: 40px; }
        .nav-menu a { color: var(--text-muted); text-decoration: none; font-size: 0.95rem; font-weight: 500; transition: color 0.2s; }
        .nav-menu a:hover { color: var(--text); }
        .nav-right { display: flex; gap: 30px; align-items: center; }
        .nav-right a { color: var(--text-muted); font-size: 0.95rem; text-decoration: none; transition: color 0.2s; }
        .nav-right a:hover { color: var(--text); }

        /* HERO SECTION */
        .hero {
            position: relative;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            text-align: left;
            overflow: hidden;
            margin-top: 0;
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
            letter-spacing: 1px;
        }
        .btn-primary:hover {
            background: white;
            color: black;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255,255,255,0.2);
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
            transition: 0.4s;
        }
        .carousel-card.active img, .carousel-card:hover img {
            filter: brightness(1.1);
        }
        .carousel-info {
            position: absolute;
            bottom: 10px;
            left: 12px;
            z-index: 2;
        }
        .carousel-name { font-size: 0.85rem; font-weight: 800; text-shadow: 0 2px 4px black; }
        .carousel-desc { font-size: 0.65rem; color: var(--text-muted); text-shadow: 0 1px 3px black; }

        /* --- CATEGORY SECTION FIX --- */
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
            aspect-ratio: 1 / 1; /* 强制正方形 */
            background: #151515;
            border: 1px solid var(--border);
            transition: all 0.4s ease;
        }
        .cat-card:hover {
            transform: translateY(-10px);
            border-color: var(--accent);
            box-shadow: 0 15px 35px var(--glow);
        }
        .cat-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
            filter: brightness(0.8);
        }
        .cat-card:hover img {
            transform: scale(1.1);
            filter: brightness(1);
        }
        /* 文字遮罩标签 - 修复显示不出字的问题 */
        .cat-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 25px 10px 15px;
            background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.5) 60%, transparent 100%);
            color: #ffffff;
            font-size: 0.9rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            z-index: 10;
            pointer-events: none;
        }

        footer {
            background: #000;
            padding: 60px 5%;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
            border-top: 1px solid var(--border);
        }

        /* RESPONSIVE */
        @media (max-width: 1100px) {
            .cat-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 768px) {
            .hero h1 { font-size: 2.5rem; }
            .cat-grid { grid-template-columns: repeat(2, 1fr); }
            .carousel-grid { justify-content: flex-start; }
        }
    </style>
</head>
<body>

    <nav>
        <div class="nav-brand">GROK CAMPING</div>
        <div class="nav-menu">
            <a href="#">PRODUCT</a>
            <a href="#">DISCOVER</a>
            <a href="#">SOFTWARE</a>
        </div>
        <div class="nav-right">
            <a href="#">Search</a>
            <a href="#">Wishlist</a>
            <?php if ($is_logged_in): ?>
                <a href="pages/profile.php">Account</a>
            <?php else: ?>
                <a href="pages/login.php">Sign In</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="hero" id="hero">
        <iframe id="hero-video" class="hero-bg-video"
            src="" 
            frameborder="0" allowfullscreen allow="autoplay; encrypted-media">
        </iframe>
        <img id="hero-img" class="hero-bg-img" src="" alt="Product Background" style="display:none;">
        <div class="hero-overlay"></div>

        <div class="hero-main-content">
            <h1 id="hero-title">PRO X2 SUPERSTRIKE</h1>
            <div class="subtitle" id="hero-subtitle">THE FASTEST CLICK</div>
            <p id="hero-desc">Revolutionary Haptic Inductive Trigger System.</p>
            <a href="#" class="btn-primary" id="hero-btn">LEARN MORE</a>
        </div>

        <div class="featured-products">
            <div class="carousel-grid">
                <div class="carousel-card active" data-index="0">
                    <img src="assets/images/pro-x-superlight-2.png" alt="Mice">
                    <div class="carousel-info">
                        <div class="carousel-name">PRO X2</div>
                        <div class="carousel-desc">Haptic Mouse</div>
                    </div>
                </div>
                <div class="carousel-card" data-index="1">
                    <img src="assets/images/Yeti1500x5.webp" alt="Audio">
                    <div class="carousel-info">
                        <div class="carousel-name">1500x5</div>
                        <div class="carousel-desc">Strong Energy</div>
                    </div>
                </div>
                <div class="carousel-card" data-index="2">
                    <img src="assets/images/racing-wheels.webp" alt="Sim">
                    <div class="carousel-info">
                        <div class="carousel-name">SIM RACING</div>
                        <div class="carousel-desc">Logitech G Edition</div>
                    </div>
                </div>
                <div class="carousel-card" data-index="3">
                    <img src="assets/images/g515-rapid-tkl.png" alt="Keyboard">
                    <div class="carousel-info">
                        <div class="carousel-name">G515 TKL</div>
                        <div class="carousel-desc">Low-Profile Analog</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="categories">
        <h2>BY CATEGORY</h2>
        <div class="cat-grid">
            <div class="cat-card">
                <img src="assets/images/mice.webp" alt="Gaming Mice">
                <div class="cat-label">GAMING MICE</div>
            </div>
            <div class="cat-card">
                <img src="assets/images/keyboard.webp" alt="Gaming Keyboards">
                <div class="cat-label">GAMING KEYBOARDS</div>
            </div>
            <div class="cat-card">
                <img src="assets/images/headsets.webp" alt="Headsets">
                <div class="cat-label">HEADSETS & AUDIO</div>
            </div>
            <div class="cat-card">
                <img src="assets/images/mousepad.webp" alt="Mousepads">
                <div class="cat-label">MOUSEPADS</div>
            </div>
            <div class="cat-card">
                <img src="assets/images/microphones.webp" alt="Microphones">
                <div class="cat-label">MICROPHONES</div>
            </div>
        </div>
    </section>

    <footer>
        <p>© 2026 Grok Gaming • Authorized Logitech G Partner • All Rights Reserved</p>
    </footer>

    <script>
    const products = [
        // YouTube 视频依然使用 iframe
        { type: 'video', src: 'https://www.youtube.com/embed/9cVWD6-BMmA?autoplay=1&mute=1&loop=1&playlist=9cVWD6-BMmA&controls=0&showinfo=0&rel=0&modestbranding=1&iv_load_policy=3',
          title: 'PRO X2 SUPERSTRIKE', subtitle: 'THE FASTEST, FULLY CUSTOMIZABLE CLICK',
          desc: 'Revolutionary Haptic Inductive Trigger System. Tunable actuation, rapid resets, pro-grade precision. Engineered for victory.',
          btn: 'LEARN MORE' },
        // 本地视频路径
        { type: 'local_video', src: 'assets/videos/goalzero.mp4',                
          title: 'Goal Zero 1500X', subtitle: 'TAKE YOUR WALL OUTLET ANYWHERE',
          desc: 'The Goal Zero 1500x comes with impressive power output for its size! It can even run power tools, Alta portable fridges, and pellet grills and has 600 W AC power out with 1,000 W surge.',
          btn: 'SHOP NOW' },
        { type: 'image', src: 'assets/images/racing-wheels.webp',
          title: 'SIM RACING SEATS', subtitle: 'ULTIMATE IMMERSION FOR RACING',
          desc: 'Playseat Challenge X or Trophy Logitech G Edition – lightweight, foldable racing seats with superior comfort.',
          btn: 'EXPLORE SEATS' },
        { type: 'image', src: 'assets/images/g515-rapid-tkl.png',
          title: 'G515 RAPID TKL', subtitle: 'LOW-PROFILE ANALOG PRECISION',
          desc: 'Slim wired gaming keyboard with fully customizable magnetic analog switches and vibrant RGB lighting.',
          btn: 'SHOP NOW' }
    ];

    // 获取 DOM 元素
    const heroContent = document.getElementById('hero');
    const heroTitle = document.getElementById('hero-title');
    const heroSubtitle = document.getElementById('hero-subtitle');
    const heroDesc = document.getElementById('hero-desc');
    const heroBtn = document.getElementById('hero-btn');
    const cards = document.querySelectorAll('.carousel-card');

    function switchProduct(index) {
        const prod = products[index];
        cards.forEach((c, i) => c.classList.toggle('active', i === index));

        // 移除现有的背景媒体元素
        const oldVideo = document.querySelector('.hero-bg-video');
        const oldImg = document.querySelector('.hero-bg-img');
        if (oldVideo) oldVideo.remove();
        if (oldImg) oldImg.remove();

        // 根据类型创建新的媒体元素
        if (prod.type === 'video') {
            // YouTube
            const iframe = document.createElement('iframe');
            iframe.className = 'hero-bg-video';
            iframe.src = prod.src;
            iframe.frameBorder = '0';
            iframe.allow = 'autoplay; encrypted-media';
            iframe.allowFullscreen = true;
            heroContent.prepend(iframe); // 插入到最前面
        } else if (prod.type === 'local_video') {
            // 本地 MP4 - 关键：添加了 autoplay, muted, loop, playsinline
            const video = document.createElement('video');
            video.className = 'hero-bg-video';
            video.autoplay = true;
            video.muted = true; // 静音
            video.loop = true;
            video.playsInline = true; // 手机端不全屏
            const source = document.createElement('source');
            source.src = prod.src;
            source.type = 'video/mp4';
            video.appendChild(source);
            heroContent.prepend(video);
        } else {
            // 图片
            const img = document.createElement('img');
            img.className = 'hero-bg-img';
            img.src = prod.src;
            img.alt = prod.title;
            heroContent.prepend(img);
        }

        // 更新文字
        heroTitle.textContent = prod.title;
        heroSubtitle.textContent = prod.subtitle;
        heroDesc.textContent = prod.desc;
        heroBtn.textContent = prod.btn;
    }

    cards.forEach(card => {
        card.addEventListener('click', () => {
            switchProduct(parseInt(card.dataset.index));
        });
    });

    // Initialize first product
    switchProduct(0);
</script>
</body>
</html>