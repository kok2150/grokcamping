<?php
session_start();
require_once '../includes/db_connect.php'; 

// 检查登录
$is_logged_in = isset($_SESSION['user_id']);
if (!$is_logged_in) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 国家代码列表
$country_codes = [
    '+60' => 'MY (+60)',
    '+65' => 'SG (+65)',
    '+86' => 'CN (+86)',
    '+1'  => 'US (+1)',
    '+886' => 'TW (+886)'
];

// 1. 获取用户资料
$user_stmt = $pdo->prepare("SELECT gcoin, username, recipient_name, shipping_country_code, phone, country_region, state, city, postal_code, address_line FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

$user_coins = $user['gcoin'] ?? 0;
$coin_value = $user_coins / 100; 

$default_name = !empty($user['recipient_name']) ? $user['recipient_name'] : ($user['username'] ?? '');
$default_cc = !empty($user['shipping_country_code']) ? $user['shipping_country_code'] : '+60';
$default_phone_num = $user['phone'] ?? '';

$address_parts = array_filter([
    $user['address_line'] ?? '',
    $user['city'] ?? '',
    $user['state'] ?? '',
    $user['postal_code'] ?? '',
    $user['country_region'] ?? ''
]);
$full_address = implode(", ", $address_parts);

// 2. 获取购物车内容
$query = "SELECT c.quantity, p.name, p.price, p.image_url FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($items)) { header("Location: cart.php"); exit(); }

$subtotal = 0;
foreach ($items as $item) { $subtotal += $item['price'] * $item['quantity']; }
$shipping = 10.00;
$total_before_discount = $subtotal + $shipping;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GROK CAMPING</title>
    <style>
        :root {
            --bg: #000000;
            --bg-dark: #0a0a0a;
            --text: #e0e0e0;
            --text-muted: #a0a0a0;
            --accent: #daf63b; /* 亮黄主题色 */
            --tng-blue: #005eb8; /* TNG 品牌蓝 */
            --border: #222222;
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background: var(--bg); color: var(--text); padding-top: 80px; }

        /* --- NAVIGATION --- */
        nav {
            position: fixed; top: 0; left: 0; right: 0;
            background: rgba(0,0,0,0.92); backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border); z-index: 1000;
            padding: 0 5%; height: 75px; display: flex;
            align-items: center; justify-content: space-between;
        }
        .nav-brand { font-size: 1.5rem; font-weight: 700; letter-spacing: 2px; color: #fff; text-decoration: none;}
        .nav-menu { display: flex; gap: 30px; }
        .nav-menu a { color: var(--text-muted); text-decoration: none; font-size: 0.85rem; font-weight: 700; letter-spacing: 1px; }
        .nav-menu a:hover { color: var(--accent); }

        /* --- LAYOUT --- */
        .checkout-container { max-width: 1250px; margin: 0 auto; display: grid; grid-template-columns: 1fr 430px; gap: 40px; padding: 40px 5%; }
        .checkout-card { background: var(--bg-dark); border: 1px solid var(--border); border-radius: 20px; padding: 30px; margin-bottom: 30px; }
        
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .section-title { font-size: 1rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: #fff; }
        .back-link { color: var(--text-muted); text-decoration: none; font-size: 0.75rem; font-weight: 700; transition: 0.3s; }
        .back-link:hover { color: var(--accent); }

        /* --- FORM --- */
        .form-group { margin-bottom: 22px; }
        .form-label { font-size: 0.7rem; font-weight: 800; color: var(--accent); text-transform: uppercase; margin-bottom: 10px; display: block; }
        .form-control { width: 100%; background: #111; border: 1px solid var(--border); border-radius: 12px; padding: 15px; color: #fff; font-size: 0.95rem; outline: none; border: 1px solid #333; }
        .form-control:focus { border-color: var(--accent); }

        /* 重点修复：国家代码与电话并排 */
        .phone-input-group { 
            display: flex; 
            gap: 12px; 
            align-items: center;
        }
        .cc-select { 
            width: 130px !important; /* 强制固定国家代码宽度 */
            flex-shrink: 0; /* 防止被压缩 */
        }
        .phone-input {
            flex-grow: 1; /* 占据剩余所有空间 */
        }

        /* --- PAYMENT METHOD --- */
        .payment-options { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px; }
        .payment-item { position: relative; }
        .payment-item input { position: absolute; opacity: 0; cursor: pointer; height: 100%; width: 100%; z-index: 2; }

        .payment-label { 
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 30px 15px; background: #0c0c0c; border: 2px solid var(--border);
            border-radius: 20px; cursor: pointer; transition: 0.4s; gap: 15px;
        }

        .payment-label img { 
            height: 45px; 
            width: auto; 
            object-fit: contain; 
            filter: brightness(1.1);
            transition: 0.4s;
        }

        .payment-text { font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }

        #pay_card:checked + .payment-label { 
            border-color: var(--accent); 
            background: rgba(218, 246, 59, 0.05);
            box-shadow: 0 0 25px rgba(218, 246, 59, 0.15);
        }
        #pay_card:checked + .payment-label .payment-text { color: var(--accent); }

        #pay_tng:checked + .payment-label { 
            border-color: var(--tng-blue); 
            background: rgba(0, 94, 184, 0.08);
            box-shadow: 0 0 25px rgba(0, 94, 184, 0.3);
        }
        #pay_tng:checked + .payment-label .payment-text { color: #fff; }

        /* --- SUMMARY & SWITCH --- */
        .switch-container { background: rgba(255,255,255,0.03); border: 1px solid var(--border); padding: 20px; border-radius: 15px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .switch { position: relative; display: inline-block; width: 44px; height: 24px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #333; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--accent); }
        input:checked + .slider:before { transform: translateX(20px); background-color: #000; }

        .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.9rem; }
        .item-mini { display: flex; gap: 15px; margin-bottom: 15px; align-items: center; border-bottom: 1px solid #111; padding-bottom: 15px; }
        .item-mini img { width: 55px; height: 55px; border-radius: 12px; background: #111; object-fit: cover; }
        
        .btn-pay { width: 100%; padding: 20px; background: var(--accent); color: #000; border: none; border-radius: 50px; font-weight: 900; font-size: 0.9rem; cursor: pointer; transition: 0.3s; text-transform: uppercase; margin-top: 15px; }
        .btn-pay:hover { background: #fff; transform: translateY(-3px); box-shadow: 0 10px 25px rgba(218, 246, 59, 0.25); }

        @media (max-width: 900px) { .checkout-container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <nav>
        <a href="../index.php" class="nav-brand">GROK CAMPING</a>
        <div class="nav-menu">
            <a href="../index.php">HOME</a>
            <a href="products.php">PRODUCT</a>
        </div>
    </nav>

    <div class="checkout-container">
        <main>
            <div class="section-header">
                <h2 class="section-title">Shipping & Payment</h2>
                <a href="cart.php" class="back-link">← RETURN TO CART</a>
            </div>

            <div class="checkout-card">
                <form id="orderForm" action="process_order.php" method="POST">
                    <input type="hidden" name="use_gcoin" id="gcoin_hidden" value="0">
                    
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($default_name) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <div class="phone-input-group">
                            <select name="shipping_country_code" class="form-control cc-select">
                                <?php foreach($country_codes as $code => $label): ?>
                                    <option value="<?= $code ?>" <?= ($default_cc == $code) ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="phone" class="form-control phone-input" placeholder="Phone Number" value="<?= htmlspecialchars($default_phone_num) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Shipping Address</label>
                        <textarea name="address" class="form-control" rows="4" placeholder="Enter full address..." required><?= htmlspecialchars($full_address) ?></textarea>
                    </div>

                    <div style="margin-top: 45px;">
                        <label class="form-label" style="margin-bottom: 20px;">Select Payment Method</label>
                        <div class="payment-options">
                            <div class="payment-item">
                                <input type="radio" name="payment_method" value="card" id="pay_card" checked>
                                <label for="pay_card" class="payment-label">
                                    <img src="../assets/images/credit-card.png" alt="Credit Card">
                                    <span class="payment-text">Credit Card</span>
                                </label>
                            </div>
                            <div class="payment-item">
                                <input type="radio" name="payment_method" value="tng" id="pay_tng">
                                <label for="pay_tng" class="payment-label">
                                    <img src="../assets/images/tng-logo.png" alt="TNG eWallet">
                                    <span class="payment-text">TNG eWallet</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>

        <aside>
            <h2 class="section-title" style="margin-bottom: 25px;">Order Summary</h2>
            <div class="checkout-card">
                <div style="max-height: 220px; overflow-y: auto; margin-bottom: 25px;">
                    <?php foreach ($items as $item): ?>
                    <div class="item-mini">
                        <img src="<?= htmlspecialchars($item['image_url'] ?: '../assets/images/placeholder.png') ?>">
                        <div style="flex:1">
                            <div style="font-size: 0.85rem; font-weight: 800;"><?= htmlspecialchars($item['name']) ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Quantity: <?= $item['quantity'] ?></div>
                        </div>
                        <div style="font-weight: 800;">$<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="switch-container">
                    <div>
                        <div style="font-size: 0.8rem; font-weight: 900; color: var(--accent);">USE G-COIN</div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);">Balance: <?= number_format($user_coins) ?> pts</div>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="useGCoin" onchange="updateTotal()">
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="summary-row">
                    <span style="color: var(--text-muted);">Subtotal</span>
                    <span>$<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span style="color: var(--text-muted);">Shipping</span>
                    <span>$<?= number_format($shipping, 2) ?></span>
                </div>
                <div id="coin-discount-row" class="summary-row" style="display:none; color: var(--accent); font-weight: 800;">
                    <span>GCoin Discount</span>
                    <span>-$<?= number_format($coin_value, 2) ?></span>
                </div>

                <hr style="border:0; border-top:1px solid var(--border); margin:15px 0;">

                <div class="summary-row" style="align-items: center;">
                    <span style="font-size: 1rem; font-weight: 800;">ORDER TOTAL</span>
                    <span id="final-total" style="font-size: 1.6rem; font-weight: 900; color: var(--accent);">$<?= number_format($total_before_discount, 2) ?></span>
                </div>

                <button type="submit" form="orderForm" class="btn-pay">Confirm Order</button>
            </div>
        </aside>
    </div>

    <script>
        const subtotal = <?= $total_before_discount ?>;
        const discount = <?= $coin_value ?>;

        function updateTotal() {
            const isChecked = document.getElementById('useGCoin').checked;
            const discountRow = document.getElementById('coin-discount-row');
            const totalDisplay = document.getElementById('final-total');
            const hiddenInput = document.getElementById('gcoin_hidden');

            if (isChecked) {
                discountRow.style.display = 'flex';
                let final = (subtotal - discount).toFixed(2);
                totalDisplay.innerText = '$' + (final < 0 ? "0.00" : final);
                hiddenInput.value = "1";
            } else {
                discountRow.style.display = 'none';
                totalDisplay.innerText = '$' + subtotal.toFixed(2);
                hiddenInput.value = "0";
            }
        }
    </script>
</body>
</html>