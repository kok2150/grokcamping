<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GROK CAMPING - <?php echo $page_title ?? 'Adventure Gear'; ?></title>
    <style>
        body {
            background: #0d1117;
            color: #e6edf3;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        header {
            background: #0f172a;
            padding: 20px 30px;
            border-bottom: 1px solid #1e293b;
        }
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        h1.logo {
            margin: 0;
            font-size: 2.4rem;
            font-weight: 900;
            letter-spacing: -1px;
            color: #ffffff;
            text-transform: uppercase;
        }
        h1.logo span {
            color: #a3e635; /* lime green accent */
        }
        nav {
            display: flex;
            align-items: center;
            gap: 28px;
            font-size: 1.05rem;
            font-weight: 500;
        }
        nav a, nav span.user-info {
            color: #e2e8f0;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        nav a:hover {
            color: #a3e635;
        }
        nav a.btn-logout {
            padding: 8px 22px;
            border: 1px solid #475569;
            border-radius: 6px;
            color: #e2e8f0;
        }
        nav a.btn-logout:hover {
            background: #a3e635;
            color: #0f172a;
            border-color: #a3e635;
        }
        nav span.user-info {
            color: #94a3b8;
        }
        main.container {
            padding: 30px 20px;
        }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <h1 class="logo">GROK <span>CAMPING</span></h1>

        <nav>
            <a href="../index.php">Home</a>
            <a href="../pages/products.php">Products</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                <a href="../pages/profile.php">Account</a>
                <a href="../pages/logout.php" class="btn-logout">Logout</a>
            <?php else: ?>
                <a href="../pages/login.php">Login</a>
                <a href="../pages/register.php" style="padding: 8px 22px; background: #a3e635; color: #0f172a; border-radius: 6px; font-weight: bold;">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container">