<?php
session_start();
require_once '../includes/db_connect.php';

// --- 1. 处理直接登出逻辑 ---
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = array(); 
    session_destroy();   
    header("Location: ../index.php"); 
    exit();
}

// 2. Login Protection
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$error = "";

// 定义国家代码列表
$country_codes = [
    '+60' => 'MY (+60)',
    '+65' => 'SG (+65)',
    '+86' => 'CN (+86)',
    '+1'  => 'US (+1)',
    '+886' => 'TW (+886)'
];

// 3. Handle POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 更新基础资料和账号绑定电话
    if (isset($_POST['update_text'])) {
        $new_name = trim($_POST['new_name']);
        $new_bio  = trim($_POST['new_status']);
        $ship_cc  = $_POST['shipping_country_code'];
        $phone_raw = trim($_POST['phone']);

        $phone = preg_replace('/^\+?\d{1,3}/', '', $phone_raw); 
        if(empty($phone)) $phone = $phone_raw; 

        $stmt = $pdo->prepare("UPDATE users SET username = ?, bio = ?, shipping_country_code = ?, phone = ? WHERE id = ?");
        if ($stmt->execute([$new_name, $new_bio, $ship_cc, $phone, $user_id])) {
            $_SESSION['username'] = $new_name;
            $msg = "Profile updated!";
        } else {
            $error = "Update failed.";
        }
    }

    // 更新 Email
    if (isset($_POST['update_email'])) {
        $new_email = filter_var($_POST['new_email'], FILTER_SANITIZE_EMAIL);
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        if($stmt->execute([$new_email, $user_id])) {
            $msg = "Email updated!";
        }
    }

    // 更新密码
    if (isset($_POST['update_pass'])) {
        $old_pass = $_POST['old_pass'];
        $new_pass = $_POST['new_pass'];
        $confirm_pass = $_POST['confirm_pass'];

        if ($new_pass !== $confirm_pass) {
            $error = "Passwords do not match.";
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $new_pass)) {
            $error = "Password security requirement not met.";
        } else {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user_data = $stmt->fetch();
            if ($user_data && password_verify($old_pass, $user_data['password'])) {
                $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $user_id]);
                $msg = "Password changed!";
            } else {
                $error = "Old password incorrect.";
            }
        }
    }

    // 头像上传
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $upload_dir = '../uploads/avatars/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $new_filename = "user_" . $user_id . "_" . time() . "." . $extension;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $new_filename)) {
            $db_path = "uploads/avatars/" . $new_filename;
            $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?")->execute([$db_path, $user_id]);
            header("Location: profile.php");
            exit();
        }
    }
}

// 4. Fetch Latest Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$gcoin_balance = $user['gcoin'] ?? 0; 
$exchange_rate = 1.00; 
$converted_val = number_format($gcoin_balance * $exchange_rate, 2);

// --- 头像路径逻辑核心修复 ---
$default_avatar = "../assets/images/default_avatar.png";
if (!empty($user['avatar'])) {
    $potential_path = "../" . $user['avatar'];
    // 检查服务器上是否真的存在该文件
    if (file_exists(__DIR__ . "/" . $potential_path)) {
        $main_avatar = $potential_path;
    } else {
        $main_avatar = $default_avatar;
    }
} else {
    $main_avatar = $default_avatar;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['username'] ?? 'User') ?> - Profile</title>
    <style>
        :root {
            --bg: #000000;
            --text: #e0e0e0;
            --text-muted: #a0a0a0;
            --accent: #daf63b; 
            --border: #222222;
            --card: rgba(20, 20, 20, 0.8);
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background: var(--bg); color: var(--text); padding-top: 75px; }

        nav {
            position: fixed; top: 0; left: 0; right: 0;
            background: rgba(0,0,0,0.92); backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border); z-index: 1000;
            padding: 0 5%; height: 75px; display: flex;
            align-items: center; justify-content: space-between;
        }
        .nav-brand { font-size: 1.5rem; font-weight: 700; color: var(--text); text-decoration: none; letter-spacing: 2px; }
        .nav-menu { display: flex; gap: 40px; }
        .nav-menu a { color: var(--text-muted); text-decoration: none; font-size: 0.95rem; font-weight: 700; }
        .nav-right { display: flex; gap: 20px; align-items: center; }
        .nav-icon { width: 22px; height: 22px; object-fit: contain; }

        .alert { 
            position: fixed; top: 100px; left: 50%; transform: translateX(-50%); z-index: 9999;
            background: var(--accent); color: #000; padding: 15px 30px; border-radius: 50px; 
            text-align: center; font-weight: bold; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            min-width: 280px; transition: opacity 0.5s ease;
        }

        .profile-wrapper { max-width: 800px; margin: 80px auto; padding: 0 20px; }
        .profile-header {
            background: var(--card); border: 1px solid var(--border); border-radius: 30px;
            padding: 40px; text-align: center; position: relative;
        }

        .avatar-container { position: relative; width: 150px; height: 150px; margin: -100px auto 20px; z-index: 1; }
        /* 增加 background-color 以防加载失败时完全透明 */
        .avatar-main { width: 100%; height: 100%; border-radius: 50%; border: 4px solid var(--accent); object-fit: cover; background-color: #1a1a1a; }
        .upload-trigger { position: absolute; bottom: 5px; right: 5px; background: var(--accent); color: #000; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-weight: bold; }

        .user-name { font-size: 2.2rem; font-weight: 800; cursor: pointer; }
        .user-bio { color: var(--text-muted); margin-bottom: 25px; cursor: pointer; }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 25px; }
        .stat-item { background: rgba(255,255,255,0.03); padding: 15px; border-radius: 15px; border: 1px solid var(--border); }
        .stat-value { font-size: 1.1rem; font-weight: 700; color: var(--accent); }
        .stat-label { font-size: 0.65rem; text-transform: uppercase; color: var(--text-muted); margin-top: 5px; }

        .settings-list { margin-top: 25px; background: var(--card); border-radius: 20px; border: 1px solid var(--border); overflow: hidden; }
        .setting-row { display: flex; justify-content: space-between; align-items: center; padding: 20px 25px; border-bottom: 1px solid var(--border); }
        .setting-info .label { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 3px; }
        .setting-info .value { font-size: 0.95rem; font-weight: 600; }

        .btn-edit { background: transparent; border: 1px solid #444; color: #fff; padding: 5px 15px; border-radius: 50px; font-size: 0.75rem; cursor: pointer; transition: 0.2s; }
        .btn-edit:hover { border-color: var(--accent); color: var(--accent); }

        .inner-panel { display: none; padding: 20px; background: #080808; border-bottom: 1px solid var(--border); }
        .edit-input { background: #111; border: 1px solid #333; color: #fff; padding: 12px; width: 100%; border-radius: 8px; margin-bottom: 12px; outline: none; font-size: 0.9rem; }
        .save-btn { background: var(--accent); color: #000; border: none; padding: 10px 20px; border-radius: 50px; font-weight: 700; cursor: pointer; width: 100%; text-transform: uppercase; }

        .policy-item { font-size: 0.8rem; color: #666; margin-bottom: 4px; transition: 0.3s; }
        .policy-item.valid { color: var(--accent); }

        .logout-row { color: #ff5555; cursor: pointer; font-weight: 700; text-align: center; padding: 20px; border-bottom: none; display: block; text-decoration: none; transition: 0.3s; }
        .logout-row:hover { background: rgba(255, 85, 85, 0.1); }

        .phone-group { display: flex; gap: 10px; margin-bottom: 12px; }
        .phone-group .cc-select { flex: 0 0 110px; }
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

    <?php if($msg): ?> <div class="alert auto-hide"><?= $msg ?></div> <?php endif; ?>
    <?php if($error): ?> <div class="alert auto-hide" style="background:#ff4444; color:#fff"><?= $error ?></div> <?php endif; ?>

    <div class="profile-wrapper">
        <div class="profile-header">
            <div class="avatar-container">
                <img src="<?= htmlspecialchars($main_avatar) ?>" 
                     class="avatar-main" 
                     id="mainAvatarImg"
                     onerror="this.onerror=null; this.src='../assets/images/default_avatar.png';">
                
                <label for="avatar-upload" class="upload-trigger">+</label>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" id="avatar-upload" name="avatar" hidden onchange="this.form.submit()">
                </form>
            </div>

            <h1 class="user-name" onclick="toggle('panel-base')"><?= htmlspecialchars($user['username'] ?? '') ?></h1>
            <p class="user-bio" onclick="toggle('panel-base')"><?= htmlspecialchars($user['bio'] ?: "Click to add a bio...") ?></p>

            <div id="panel-base" class="inner-panel" style="background:transparent; text-align: left;">
                <form method="POST">
                    <label class="label">Username</label>
                    <input type="text" name="new_name" class="edit-input" value="<?= htmlspecialchars($user['username'] ?? '') ?>">
                    <label class="label">Bio</label>
                    <textarea name="new_status" class="edit-input"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    <input type="hidden" name="shipping_country_code" value="<?= htmlspecialchars($user['shipping_country_code'] ?? '+60') ?>">
                    <input type="hidden" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    <button type="submit" name="update_text" class="save-btn">save profile</button>
                </form>
            </div>

            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value"><?= $gcoin_balance ?> <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 400;">(RM <?= $converted_val ?>)</span></div>
                    <div class="stat-label">G-Coin Balance</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= strtoupper($user['role'] ?? 'MEMBER') ?></div>
                    <div class="stat-label">Membership</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">2026</div>
                    <div class="stat-label">Joined</div>
                </div>
            </div>
        </div>

        <div class="settings-list">
            <div class="setting-row">
                <div class="setting-info">
                    <div class="label">Email Address</div>
                    <div class="value"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                </div>
                <button class="btn-edit" onclick="toggle('panel-email')">edit</button>
            </div>
            <div id="panel-email" class="inner-panel">
                <form method="POST">
                    <input type="email" name="new_email" class="edit-input" placeholder="New Email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    <button type="submit" name="update_email" class="save-btn">update email</button>
                </form>
            </div>

            <div class="setting-row">
                <div class="setting-info">
                    <div class="label">Linked Phone</div>
                    <div class="value">
                        <?php 
                        if (!empty($user['phone'])) {
                            echo htmlspecialchars($user['shipping_country_code'] . ' ' . $user['phone']);
                        } else {
                            echo 'No phone linked';
                        }
                        ?>
                    </div>
                </div>
                <button class="btn-edit" onclick="toggle('panel-account-phone')">edit</button>
            </div>
            <div id="panel-account-phone" class="inner-panel">
                <form method="POST">
                    <div class="phone-group">
                        <select name="shipping_country_code" class="edit-input cc-select">
                            <?php foreach($country_codes as $code => $label): ?>
                                <option value="<?= $code ?>" <?= (($user['shipping_country_code'] ?? '+60') == $code) ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="phone" class="edit-input" placeholder="Phone Number" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                    </div>
                    <input type="hidden" name="new_name" value="<?= htmlspecialchars($user['username'] ?? '') ?>">
                    <input type="hidden" name="new_status" value="<?= htmlspecialchars($user['bio'] ?? '') ?>">
                    <button type="submit" name="update_text" class="save-btn">update phone</button>
                </form>
            </div>
            
            <div class="setting-row">
                <div class="setting-info">
                    <div class="label">My Address</div>
                    <div class="value">
                        <?= !empty($user['address_line']) ? htmlspecialchars(($user['recipient_name'] ?? '') . " | " . ($user['city'] ?? '')) : "No address set" ?>
                    </div>
                </div>
                <a href="address.php" class="btn-edit" style="text-decoration: none;">manage</a>
            </div>

            <div class="setting-row">
                <div class="setting-info">
                    <div class="label">password</div>
                    <div class="value">*******</div>
                </div>
                <button class="btn-edit" onclick="toggle('panel-pass')">edit</button>
            </div>
            <div id="panel-pass" class="inner-panel">
                <form method="POST">
                    <input type="password" name="old_pass" class="edit-input" placeholder="Current Password" required>
                    <input type="password" id="new_pass_input" name="new_pass" class="edit-input" placeholder="New Password" required>
                    <div style="padding:0 5px 10px;">
                        <div id="p-len" class="policy-item">• At least 8 characters</div>
                        <div id="p-cap" class="policy-item">• At least one uppercase (A-Z)</div>
                        <div id="p-num" class="policy-item">• At least one number (0-9)</div>
                    </div>
                    <input type="password" name="confirm_pass" class="edit-input" placeholder="Confirm New Password" required>
                    <button type="submit" name="update_pass" class="save-btn">update password</button>
                </form>
            </div>

            <a href="profile.php?action=logout" class="logout-row">LOGOUT</a>
        </div>
    </div>

    <script>
        function toggle(id) {
            const el = document.getElementById(id);
            const isVisible = el.style.display === 'block';
            el.style.display = isVisible ? 'none' : 'block';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const alerts = document.querySelectorAll('.auto-hide');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 3000);
            });
        });

        const newPassInput = document.getElementById('new_pass_input');
        if(newPassInput) {
            newPassInput.addEventListener('input', function() {
                const val = this.value;
                document.getElementById('p-len').classList.toggle('valid', val.length >= 8);
                document.getElementById('p-cap').classList.toggle('valid', /[A-Z]/.test(val));
                document.getElementById('p-num').classList.toggle('valid', /[0-9]/.test(val));
            });
        }
    </script>
</body>
</html>