<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

// 如果已经登录，直接跳转
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db_connect.php';
$error = '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id_alias = trim($_POST['user_id_alias'] ?? '');
    $username      = trim($_POST['username'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $country_code  = $_POST['country_code'] ?? '+60'; 
    $phone_input   = trim($_POST['phone'] ?? '');     

    $password      = $_POST['password'] ?? '';
    $confirm       = $_POST['confirm_password'] ?? '';

    // --- 核心修正：数据清洗与分开存储 ---
    // 1. 只保留电话号码中的数字（防止用户输入空格、连字符等）
    $clean_phone = preg_replace('/\D/', '', $phone_input); 
    
    // 2. 检查用户是否在号码框里重复输入了国家代码数字部分（如 6011...）
    $prefix_digits = preg_replace('/\D/', '', $country_code); 
    if (!empty($clean_phone) && strpos($clean_phone, $prefix_digits) === 0) {
        // 如果号码是以国家代码开头的，就截取掉开头，只保留纯手机号部分
        $clean_phone = substr($clean_phone, strlen($prefix_digits));
    }

    if (empty($user_id_alias) || empty($username) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } 
    elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } 
    elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $error = "Password does not meet security requirements.";
    } else {
        try {
            // 检查重复
            $stmt = $pdo->prepare("SELECT id FROM users WHERE user_id_alias = ? OR email = ? OR username = ?");
            $stmt->execute([$user_id_alias, $email, $username]);
            
            if ($stmt->fetch()) {
                $error = "User ID, Username or Email already taken.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $default_avatar = "assets/images/default-avatar.jpg"; 
                $default_bio = "Adventure awaits...";

                // --- 核心插入逻辑：适配您的数据库列名 ---
                $sql = "INSERT INTO users (
                            user_id_alias, 
                            username, 
                            email, 
                            phone, 
                            shipping_country_code, 
                            account_country_code, 
                            country_code,
                            password, 
                            avatar,
                            bio
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                
                // 执行插入
                $params = [
                    $user_id_alias, 
                    $username, 
                    $email, 
                    $clean_phone,    // 存入纯号码 (如: 1135894737)
                    $country_code,   // 存入 +60
                    $country_code,   // 存入 +60
                    $country_code,   // 存入 +60 (适配您表中冗余的 country_code 列)
                    $hashed, 
                    $default_avatar,
                    $default_bio
                ];

                if ($stmt->execute($params)) {
                    $msg = "Account created! Redirecting to login...";
                    header("refresh:2;url=login.php");
                }
            }
        } catch (PDOException $e) {
            // 调试用：如果报错可以显示 $e->getMessage()
            $error = "Database error. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - GROK CAMPING</title>
    <style>
        :root {
            --bg: #000000;
            --accent: #daf63b;
            --accent-glow: rgba(218, 246, 59, 0.2);
            --text-muted: #888888;
            --border: #222222;
            --card: rgba(10, 10, 10, 0.95);
            --error: #ff4444;
            --success: #00ff88;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--bg); color: #fff; font-family: 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh; display: flex; justify-content: center; align-items: center;
            position: relative; padding: 60px 0;
        }

        body::before {
            content: ""; position: absolute; width: 400px; height: 400px;
            background: var(--accent); filter: blur(150px); opacity: 0.08;
            top: -100px; right: -100px; z-index: 0;
        }

        .box {
            position: relative; z-index: 10;
            background: var(--card); border: 1px solid var(--border);
            border-radius: 24px; padding: 50px 40px;
            width: 100%; max-width: 480px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.8);
            backdrop-filter: blur(15px);
        }

        h1 { font-size: 2.2rem; font-weight: 800; letter-spacing: -1px; text-transform: uppercase; margin-bottom: 8px; }
        .msg { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 30px; }

        form { display: flex; flex-direction: column; gap: 18px; }
        .group { display: flex; flex-direction: column; gap: 8px; }
        label { font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; }

        .phone-input-container { display: flex; gap: 10px; }
        
        select {
            width: 120px; padding: 14px; background: #111; border: 1px solid #222;
            border-radius: 12px; color: #fff; font-size: 0.95rem; cursor: pointer;
            transition: 0.3s; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23888' viewBox='0 0 24 24'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 12px center;
        }

        input {
            flex: 1; padding: 14px; background: #111; border: 1px solid #222;
            border-radius: 12px; color: #fff; font-size: 0.95rem; transition: 0.3s;
        }
        input:focus, select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 15px var(--accent-glow); }

        .policy-box { margin-top: 5px; display: flex; flex-direction: column; gap: 6px; }
        .policy-item { color: #444; font-size: 0.75rem; display: flex; align-items: center; transition: 0.3s; }
        .policy-item::before { content: '○'; margin-right: 10px; font-weight: bold; }
        .policy-item.valid { color: var(--success); }
        .policy-item.valid::before { content: '●'; }

        button {
            margin-top: 10px; padding: 16px; background: var(--accent);
            color: #000; border: none; border-radius: 50px;
            font-size: 1rem; font-weight: 800; text-transform: uppercase;
            cursor: pointer; transition: 0.3s;
        }
        button:hover { transform: scale(1.02); }
        button:disabled { opacity: 0.3; cursor: not-allowed; filter: grayscale(1); }

        .extra { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #1a1a1a; font-size: 0.9rem; }
        .extra a { color: var(--accent); text-decoration: none; font-weight: 600; }
        
        .alert { padding: 12px; margin-bottom: 20px; font-size: 0.85rem; border-radius: 8px; border-left: 3px solid; }
        .alert-error { background: rgba(255, 68, 68, 0.1); border-color: var(--error); color: var(--error); }
        .alert-success { background: rgba(0, 255, 136, 0.1); border-color: var(--success); color: var(--success); }
        .note { font-size: 0.65rem; color: #555; margin-top: -4px; }
    </style>
</head>
<body>

<div class="box">
    <h1>Create Account</h1>
    <p class="msg">Join the community of gear commanders.</p>

    <?php if ($error): ?> <div class="alert alert-error"><?= htmlspecialchars($error) ?></div> <?php endif; ?>
    <?php if ($msg): ?> <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div> <?php endif; ?>

    <form method="POST" id="regForm">
        <div class="group">
            <label>User ID</label>
            <input type="text" name="user_id_alias" placeholder="Permanent ID" required>
            <span class="note">Permanent ID cannot be changed later.</span>
        </div>

        <div class="group">
            <label>Username</label>
            <input type="text" name="username" placeholder="Display Name" required>
        </div>

        <div class="group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="commander@gear.com" required>
        </div>

        <div class="group">
            <label>Phone Number (Optional)</label>
            <div class="phone-input-container">
                <select name="country_code">
                    <option value="+60">+60 (MY)</option>
                    <option value="+65">+65 (SG)</option>
                    <option value="+86">+86 (CN)</option>
                    <option value="+1">+1 (US)</option>
                    <option value="+886">+886 (TW)</option>
                </select>
                <input type="tel" name="phone" placeholder="1135894737">
            </div>
        </div>

        <div class="group">
            <label>Password</label>
            <input type="password" name="password" id="password" placeholder="••••••••" required>
            <div class="policy-box">
                <div id="p-len" class="policy-item">At least 8 characters long</div>
                <div id="p-cap" class="policy-item">At least one uppercase letter (A-Z)</div>
                <div id="p-num" class="policy-item">At least one number (0-9)</div>
                <div id="p-spec" class="policy-item">At least one special character (@, #, etc.)</div>
            </div>
        </div>

        <div class="group">
            <label>Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
        </div>
        
        <button type="submit" id="submitBtn" disabled>Create Account</button>
    </form>

    <div class="extra">
        <p>Already have an account? <a href="login.php">Sign In</a></p>
    </div>
</div>

<script>
    const password = document.getElementById('password');
    const confirmPass = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submitBtn');
    const policies = {
        len:  document.getElementById('p-len'),
        cap:  document.getElementById('p-cap'),
        num:  document.getElementById('p-num'),
        spec: document.getElementById('p-spec')
    };

    function validate() {
        const val = password.value;
        const conf = confirmPass.value;
        const results = {
            len: val.length >= 8,
            cap: /[A-Z]/.test(val),
            num: /[0-9]/.test(val),
            spec: /[\W_]/.test(val)
        };
        
        // 更新 UI 反馈
        Object.keys(results).forEach(key => policies[key].classList.toggle('valid', results[key]));
        
        const allValid = Object.values(results).every(Boolean);
        const match = val === conf && val !== "";
        
        // 确认密码框颜色反馈
        confirmPass.style.borderColor = match ? "var(--success)" : (conf === "" ? "#222" : "var(--error)");
        
        // 只有全部满足且密码匹配才启用按钮
        submitBtn.disabled = !(allValid && match);
    }

    password.addEventListener('input', validate);
    confirmPass.addEventListener('input', validate);
</script>
</body>
</html>