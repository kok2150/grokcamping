<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grok Gaming - <?php echo $page_title ?? 'Logitech Gaming Gear'; ?></title>
    <style>
        body { background: #0d1117; color: #c9d1d9; font-family: Arial, sans-serif; margin: 0; }
        header { background: #161b22; padding: 15px; text-align: center; }
        nav a { color: #58a6ff; margin: 0 15px; text-decoration: none; font-weight: bold; }
        nav a:hover { color: #238636; }
        main { padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
    </style>
</head>
<body>

<header>
    <h1>Grok Gaming</h1>
    <nav>
        <a href="../index.php">Home</a>
        <a href="../pages/products.php">Products</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../pages/logout.php">Logout</a>
        <?php else: ?>
            <a href="../pages/login.php">Login</a>
            <a href="../pages/register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<main class="container">