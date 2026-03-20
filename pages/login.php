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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 这里的变量名改为 login_input，因为它可以是 email 也可以是 user_id_alias
    $login_input = trim($_POST['login_input'] ?? '');
    $password    = $_POST['password'] ?? '';

    if (empty($login_input) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            // --- 核心修正：同时匹配 email 和 user_id_alias ---
            $sql = "SELECT id, username, email, password FROM users WHERE email = ? OR user_id_alias = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$login_input, $login_input]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['logged_in'] = true;
                header("Location: ../index.php");
                exit();
            } else {
                $error = "Invalid credentials or password.";
            }
        } catch (PDOException $e) {
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
    <title>Login - GROK CAMPING</title>
    <style>
        :root {
            --bg: #000000;
            --accent: #daf63b;
            --accent-glow: rgba(218, 246, 59, 0.2);
            --text-muted: #888888;
            --border: #222222;
            --card: rgba(10, 10, 10, 0.95);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--bg);
            color: #fff;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: ""; position: absolute; width: 400px; height: 400px;
            background: var(--accent); filter: blur(150px); opacity: 0.08;
            top: -100px; right: -100px; z-index: 0;
        }
        body::after {
            content: ""; position: absolute; width: 400px; height: 400px;
            background: #3b82f6; filter: blur(180px); opacity: 0.06;
            bottom: -100px; left: -100px; z-index: 0;
        }

        .back-nav {
            position: absolute; top: 40px; left: 40px;
            color: var(--text-muted); text-decoration: none;
            font-size: 0.85rem; font-weight: 700; letter-spacing: 1.5px;
            z-index: 20; transition: 0.3s;
        }
        .back-nav:hover { color: var(--accent); text-shadow: 0 0 10px var(--accent-glow); }

        .box {
            position: relative; z-index: 10;
            background: var(--card); border: 1px solid var(--border);
            border-radius: 24px; padding: 50px 40px;
            width: 100%; max-width: 440px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.8);
            backdrop-filter: blur(15px);
        }

        h1 { font-size: 2.2rem; font-weight: 800; letter-spacing: -1px; text-transform: uppercase; margin-bottom: 8px; }
        .msg { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 30px; }

        .error-msg {
            background: rgba(255, 68, 68, 0.1); border-left: 3px solid #ff4444;
            color: #ff4444; padding: 12px; margin-bottom: 20px; font-size: 0.85rem; border-radius: 4px;
        }

        form { display: flex; flex-direction: column; gap: 20px; }
        .group { display: flex; flex-direction: column; gap: 8px; }
        label { font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; }

        input {
            padding: 16px; background: #111; border: 1px solid #222;
            border-radius: 12px; color: #fff; font-size: 1rem; transition: 0.3s;
        }
        input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 15px var(--accent-glow); }

        button {
            margin-top: 10px; padding: 16px; background: var(--accent);
            color: #000; border: none; border-radius: 50px;
            font-size: 1rem; font-weight: 800; text-transform: uppercase;
            cursor: pointer; transition: 0.3s;
        }
        button:hover { background: #fff; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(218, 246, 59, 0.2); }

        .extra { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #1a1a1a; font-size: 0.9rem; }
        .extra a { color: var(--accent); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<a href="../index.php" class="back-nav">← BACK TO EXPLORE</a>

<div class="box">
    <h1>Sign In</h1>
    <p class="msg">Enter your gear credentials to continue.</p>

    <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="group">
            <label>Email or User ID</label>
            <input type="text" name="login_input" placeholder="Email or User ID" required autofocus>
        </div>
        <div class="group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit">Login</button>
    </form>

    <div class="extra">
        <p>Don't have an account? <a href="register.php">Create Account</a></p>
    </div>
</div>

</body>
</html>