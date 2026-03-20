<?php
session_start();
require_once '../includes/db_connect.php';

// 1. 登录保护
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$error = "";

$country_codes = [
    '+60' => 'MY (+60)',
    '+65' => 'SG (+65)',
    '+86' => 'CN (+86)',
    '+1'  => 'US (+1)',
    '+886' => 'TW (+886)'
];

// 2. 处理地址保存逻辑
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_address'])) {
    $name = trim($_POST['recipient_name']);
    $ship_cc = $_POST['shipping_country_code'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address_line']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $postcode = trim($_POST['postal_code']);
    $region = $_POST['country_region'];

    $sql = "UPDATE users SET recipient_name = ?, shipping_country_code = ?, phone = ?, address_line = ?, city = ?, state = ?, postal_code = ?, country_region = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if($stmt->execute([$name, $ship_cc, $phone, $address, $city, $state, $postcode, $region, $user_id])) {
        $msg = "Address saved successfully!";
    } else {
        $error = "Failed to save address.";
    }
}

// 3. 获取最新地址数据
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Address - GROK CAMPING</title>
    <style>
        :root {
            --bg: #000000; --text: #e0e0e0; --text-muted: #a0a0a0;
            --accent: #daf63b; --border: #222222; --card: rgba(20, 20, 20, 0.8);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background: var(--bg); color: var(--text); padding-top: 100px; }
        
        /* 导航栏样式复用 */
        nav { position: fixed; top: 0; left: 0; right: 0; background: rgba(0,0,0,0.92); backdrop-filter: blur(16px); border-bottom: 1px solid var(--border); z-index: 1000; padding: 0 5%; height: 75px; display: flex; align-items: center; justify-content: space-between; }
        .nav-brand { font-size: 1.5rem; font-weight: 700; color: var(--text); text-decoration: none; letter-spacing: 2px; }
        
        .wrapper { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .back-link { color: var(--accent); text-decoration: none; font-size: 0.9rem; font-weight: bold; }
        
        .address-card { background: var(--card); border: 1px solid var(--border); border-radius: 20px; padding: 30px; }
        .label { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; display: block; }
        .edit-input { background: #111; border: 1px solid #333; color: #fff; padding: 14px; width: 100%; border-radius: 8px; margin-bottom: 20px; outline: none; font-size: 0.95rem; }
        .edit-input:focus { border-color: var(--accent); }
        
        .phone-group { display: flex; gap: 10px; }
        .cc-select { flex: 0 0 110px; }
        .address-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        
        .save-btn { background: var(--accent); color: #000; border: none; padding: 15px; border-radius: 50px; font-weight: 800; cursor: pointer; width: 100%; text-transform: uppercase; margin-top: 10px; transition: 0.3s; }
        .save-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(218, 246, 59, 0.3); }

        .alert { position: fixed; top: 90px; left: 50%; transform: translateX(-50%); z-index: 9999; background: var(--accent); color: #000; padding: 12px 25px; border-radius: 50px; font-weight: bold; transition: 0.5s; }
    </style>
</head>
<body>

    <nav>
        <a href="../index.php" class="nav-brand">GROK CAMPING</a>
        <div class="nav-right">
            <a href="profile.php" style="color:white; text-decoration:none;">BACK TO PROFILE</a>
        </div>
    </nav>

    <?php if($msg): ?> <div class="alert" id="auto-hide"><?= $msg ?></div> <?php endif; ?>

    <div class="wrapper">
        <div class="header-flex">
            <h2>Shipping Address</h2>
            <a href="profile.php" class="back-link">← Cancel</a>
        </div>

        <div class="address-card">
            <form method="POST">
                <label class="label">Full Name</label>
                <input type="text" name="recipient_name" class="edit-input" placeholder="Recipient Name" value="<?= htmlspecialchars($user['recipient_name'] ?? '') ?>" required>
                
                <label class="label">Contact Phone</label>
                <div class="phone-group">
                    <select name="shipping_country_code" class="edit-input cc-select">
                        <?php foreach($country_codes as $code => $label): ?>
                            <option value="<?= $code ?>" <?= ($user['shipping_country_code'] == $code) ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="phone" class="edit-input" placeholder="Phone Number" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                </div>

                <label class="label">Country / Region</label>
                <select name="country_region" class="edit-input" required>
                    <option value="">Select Country</option>
                    <option value="Malaysia" <?= ($user['country_region'] == 'Malaysia') ? 'selected' : '' ?>>Malaysia</option>
                    <option value="Singapore" <?= ($user['country_region'] == 'Singapore') ? 'selected' : '' ?>>Singapore</option>
                    <option value="China" <?= ($user['country_region'] == 'China') ? 'selected' : '' ?>>China</option>
                </select>

                <div class="address-grid">
                    <div>
                        <label class="label">State / Province</label>
                        <input type="text" name="state" class="edit-input" placeholder="State" value="<?= htmlspecialchars($user['state'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label class="label">City</label>
                        <input type="text" name="city" class="edit-input" placeholder="City" value="<?= htmlspecialchars($user['city'] ?? '') ?>" required>
                    </div>
                </div>

                <label class="label">Postal Code</label>
                <input type="text" name="postal_code" class="edit-input" placeholder="Postal Code" value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>" required>

                <label class="label">Street Address</label>
                <input type="text" name="address_line" class="edit-input" placeholder="House No, Street Name, Building..." value="<?= htmlspecialchars($user['address_line'] ?? '') ?>" required>

                <button type="submit" name="update_address" class="save-btn">Save Address</button>
            </form>
        </div>
    </div>

    <script>
        // 提示信息 3 秒后自动消失
        const alertBox = document.getElementById('auto-hide');
        if (alertBox) {
            setTimeout(() => {
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.remove(), 500);
            }, 3000);
        }
    </script>
</body>
</html>