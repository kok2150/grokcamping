<?php
session_start();
require_once '../includes/db_connect.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_logged_in = true;

$query = "SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.image_url 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - GROK CAMPING</title>
    <style>
        :root {
            --bg: #000000;
            --bg-dark: #0a0a0a;
            --text: #e0e0e0;
            --text-muted: #a0a0a0;
            --accent: #daf63b;
            --border: #222222;
            --glow: rgba(218, 246, 59, 0.25);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background: var(--bg); color: var(--text); padding-top: 75px; }

        /* --- NAV --- */
        nav {
            position: fixed; top: 0; left: 0; right: 0;
            background: rgba(0,0,0,0.92); backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border); z-index: 1000;
            padding: 0 5%; height: 75px; display: flex;
            align-items: center; justify-content: space-between;
        }
        .nav-brand { font-size: 1.5rem; font-weight: 700; letter-spacing: 2px; color: var(--text); text-decoration: none;}
        .nav-menu { display: flex; gap: 40px; }
        .nav-menu a { color: var(--text-muted); text-decoration: none; font-size: 0.95rem; font-weight: 700; padding-top: 15px; display: inline-block; }
        .nav-menu a:hover { color: var(--accent); }
        .nav-right { display: flex; gap: 20px; align-items: center; }
        .nav-icon { width: 22px; height: 22px; object-fit: contain; }

        /* --- CONTENT --- */
        .cart-container { max-width: 1100px; margin: 60px auto; padding: 0 5%; }
        .cart-header { margin-bottom: 40px; border-bottom: 1px solid var(--border); padding-bottom: 20px; }
        .cart-header h1 { font-size: 2.2rem; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; }

        .cart-item {
            display: grid; grid-template-columns: 120px 1fr auto auto;
            align-items: center; gap: 30px;
            background: var(--bg-dark); border: 1px solid var(--border);
            padding: 20px; border-radius: 24px; margin-bottom: 20px;
        }
        
        .item-img-box { width: 100px; height: 100px; background: #111; border-radius: 12px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .item-img-box img { max-width: 90%; height: auto; }

        .item-info h3 { font-size: 1.1rem; margin-bottom: 8px; color: #fff; }
        .item-info p { color: var(--accent); font-weight: 800; }
        
        .quantity-controls { display: flex; align-items: center; gap: 15px; background: rgba(255,255,255,0.05); padding: 8px 15px; border-radius: 50px; border: 1px solid var(--border); }
        .qty-btn { background: transparent; border: none; color: white; font-size: 1.2rem; cursor: pointer; width: 25px; font-weight: bold; }
        .qty-btn:hover { color: var(--accent); }

        .remove-btn { color: var(--text-muted); cursor: pointer; text-decoration: none; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-left: 20px; }
        .remove-btn:hover { color: #ff4444; }

        .cart-summary { margin-top: 40px; background: var(--bg-dark); padding: 40px; border-radius: 24px; border: 1px solid var(--border); text-align: right; }
        .total-row { font-size: 1.8rem; font-weight: 800; margin-bottom: 25px; }
        .checkout-btn { background: var(--accent); color: black; padding: 18px 60px; border-radius: 50px; text-decoration: none; font-weight: 800; display: inline-block; text-transform: uppercase; font-size: 0.85rem; }
    </style>
</head>
<body>

    <nav>
        <a href="../index.php" class="nav-brand">GROK CAMPING</a>
        <div class="nav-menu">
            <a href="../index.php">HOME</a>
            <a href="products.php">PRODUCT</a>
            <a href="contact.php">CONTACT US</a>
        </div>
        <div class="nav-right">
            <a href="wishlist.php" class="icon-link"><img src="../assets/images/wishlist-icon.png" class="nav-icon"></a>
            <a href="cart.php" class="icon-link"><img src="../assets/images/cart-icon.png" class="nav-icon"></a>
            <a href="profile.php" class="icon-link"><img src="../assets/images/user-icon.png" class="nav-icon"></a>
        </div>
    </nav>

    <div class="cart-container">
        <div class="cart-header">
            <h1>Gear Bag<span style="color: var(--accent);">.</span></h1>
        </div>

        <?php if (empty($cart_items)): ?>
            <div style="text-align: center; padding: 100px 0;">
                <p style="color: var(--text-muted); margin-bottom: 30px;">Your bag is currently empty.</p>
                <a href="products.php" class="checkout-btn">Back to Shop</a>
            </div>
        <?php else: ?>
            <div class="cart-list">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <div class="item-img-box">
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="Product">
                        </div>
                        <div class="item-info">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <p>$<?= number_format($item['price'], 2) ?></p>
                        </div>
                        <div class="quantity-controls">
                            <button class="qty-btn" onclick="handleCart(<?= $item['cart_id'] ?>, 'update', <?= $item['quantity'] - 1 ?>)">-</button>
                            <span><?= $item['quantity'] ?></span>
                            <button class="qty-btn" onclick="handleCart(<?= $item['cart_id'] ?>, 'update', <?= $item['quantity'] + 1 ?>)">+</button>
                        </div>
                        <a href="javascript:void(0)" onclick="handleCart(<?= $item['cart_id'] ?>, 'delete')" class="remove-btn">Remove</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <div class="total-row">
                    <span style="color: var(--text-muted); font-size: 1rem;">Total:</span>
                    $<?= number_format($total_price, 2) ?>
                </div>
                <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
    // 统一处理函数：加、减、删除都走这里
    function handleCart(cartId, action, newQty = 0) {
        if (action === 'update' && newQty < 1) return;
        if (action === 'delete' && !confirm('Remove this item?')) return;

        // 发送请求给后端处理逻辑
        fetch(`update_cart.php?id=${cartId}&action=${action}&quantity=${newQty}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload(); // 操作成功后刷新页面看到新数据
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => console.error('Fetch error:', err));
    }
    </script>
</body>
</html>