<?php
// Secure session settings - put at the very top
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);      // change to 1 in production with HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Include DB connection (uncomment when ready)
require_once '../includes/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['logged_in'] = true;

                header("Location: ../index.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "Database error. Please try again later.";
            // In production: error_log($e->getMessage());
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
            --bg-dark: #0a0a0a;
            --text: #e0e0e0;
            --text-muted: #a0a0a0;
            --accent: #daf63b;
            --accent-dark: #c2d92f;
            --border: #222222;
            --card: rgba(20, 20, 20, 0.9);
            --error: #ff5555;
        }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-box {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 40px 35px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6);
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.1rem;
            letter-spacing: 1px;
        }
        .error {
            color: var(--error);
            text-align: center;
            margin-bottom: 20px;
            font-weight: 500;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        label {
            font-weight: 500;
            color: var(--text-muted);
        }
        input {
            padding: 14px 16px;
            background: #111;
            border: 1px solid #333;
            border-radius: 6px;
            color: white;
            font-size: 1rem;
        }
        input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(218, 246, 59, 0.15);
        }
        button {
            padding: 14px;
            background: var(--accent);
            color: #000;
            border: none;
            border-radius: 50px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s;
        }
        button:hover {
            background: var(--accent-dark);
            transform: translateY(-2px);
        }
        .extra {
            text-align: center;
            margin-top: 25px;
            font-size: 0.95rem;
        }
        .extra a {
            color: var(--accent);
            text-decoration: none;
        }
        .extra a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="login-box">
    <h1>Sign In</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required autofocus>
        </div>

        <div>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">Sign In</button>
    </form>

    <div class="extra">
        <p>Don't have an account? <a href="register.php">Sign Up</a></p>
        <p><a href="../index.php">← Back to Home</a></p>
    </div>
</div>

</body>
</html>