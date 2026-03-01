<?php
// ────────────────────────────────────────────────
//  Updated profile.php – Grok Camping + Editable Name & Bio (size 100% locked)
// ────────────────────────────────────────────────

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id']) && !isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

// Handle name & bio edit (via POST from JS form)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_name'])) {
        $_SESSION['username'] = trim($_POST['new_name']);
    }
    if (isset($_POST['new_status'])) {
        $_SESSION['status'] = trim($_POST['new_status']);
    }
    header("Location: profile.php");
    exit();
}

// Load from session (or default)
$username = $_SESSION['username'] ?? 'User';
$status   = $_SESSION['status'] ?? "Adventure awaits • Gym • Explore";
$phone    = $_SESSION['phone']    ?? '+60 12-3456789';
$avatar   = $_SESSION['avatar']   ?? 'uploads/avatars/default-avatar.jpg';

// Handle avatar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $upload_dir = 'uploads/avatars/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $file_name = time() . '_' . basename($_FILES['avatar']['name']);
    $target    = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
        $_SESSION['avatar'] = $target;
        $avatar = $target;
    } else {
        $upload_error = "Upload failed. Check folder permissions.";
    }
}

// Try to include header
$header_included = false;
if (file_exists('../includes/header.php')) {
    include '../includes/header.php';
    $header_included = true;
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Profile – Grok Camping</title>
        <style>
            body { background:#0d1117; color:#e6edf3; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif; margin:0; padding:0; position:relative; overflow-x:hidden; }
        </style>
    </head>
    <body>
    <?php
}
?>

<style>
    body { position: relative; }
    .video-bg { position: fixed !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; object-fit: cover !important; z-index: -10 !important; }
    .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.25); z-index: -1; }
    .profile-top { background: rgba(15, 23, 42, 0.25); padding: 70px 20px 50px; text-align: center; backdrop-filter: blur(4px); border-bottom: 1px solid rgba(255,255,255,0.06); position: relative; z-index: 1; }
    .card { background: rgba(22, 27, 34, 0.30); margin: 30px 20px; border-radius: 20px; overflow: hidden; backdrop-filter: blur(6px); border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 8px 32px rgba(0,0,0,0.25); position: relative; z-index: 1; }
    .avatar-wrap { position: relative; width: 130px; height: 130px; margin: 0 auto 20px; }
    .avatar { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 4px solid #a3e635; box-shadow: 0 6px 25px rgba(0,0,0,0.6); }
    .upload-label { position: absolute; bottom: 10px; right: 10px; background: #a3e635; color: #0f172a; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; cursor: pointer; box-shadow: 0 4px 15px rgba(163,230,53,0.5); }
    #avatar-form { display: none; }

    .name {
        margin: 15px 0 8px;
        font-size: 2.6rem;
        font-weight: 700;
        color: #ffffff;
        cursor: pointer;
        line-height: 1;
        padding: 0;
        white-space: nowrap;
    }
    .status {
        font-size: 1.2rem;
        color: #c9d1d9;
        margin: 0;
        cursor: pointer;
        line-height: 1.4;
        padding: 0;
    }

    /* Locked edit fields - no size change ever */
    .name-input {
        width: auto !important;
        min-width: 150px;
        max-width: 400px;
        height: 52px !important;               /* exact height */
        font-size: 2.6rem !important;
        font-weight: 700 !important;
        line-height: 1 !important;
        padding: 0 12px !important;
        margin: 15px 0 8px !important;
        color: #ffffff !important;
        background: rgba(30, 41, 59, 0.8) !important;
        border: 1px solid #a3e635 !important;
        border-radius: 8px !important;
        box-sizing: border-box !important;
        outline: none !important;
        -webkit-appearance: none !important;
        appearance: none !important;
        box-shadow: none !important;
        text-align: center;
        overflow: hidden !important;
    }

    .status-input {
        width: 100% !important;
        max-width: 500px;
        height: 60px !important;               /* fixed height */
        font-size: 1.2rem !important;
        line-height: 1.4 !important;
        padding: 8px 12px !important;
        margin: 10px 0 !important;
        color: #c9d1d9 !important;
        background: rgba(30, 41, 59, 0.8) !important;
        border: 1px solid #a3e635 !important;
        border-radius: 8px !important;
        box-sizing: border-box !important;
        resize: none !important;
        overflow: hidden !important;
        outline: none !important;
        -webkit-appearance: none !important;
        appearance: none !important;
        box-shadow: none !important;
    }

    .save-btn {
        background: #a3e635;
        color: #0f172a;
        border: none;
        padding: 8px 24px;
        border-radius: 20px;
        cursor: pointer;
        font-weight: bold;
        margin-top: 10px;
    }

    /* Remove any focus styles that add size */
    .name-input:focus, .status-input:focus {
        outline: none !important;
        box-shadow: none !important;
        border-color: #a3e635 !important;
        padding: 0 12px !important;
    }

    .card-item, .settings-item { padding: 22px 24px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; gap: 20px; font-size: 1.1rem; font-weight: 500; }
    .card-item:last-child, .settings-item:last-child { border-bottom: none; }
    .icon { font-size: 1.6rem; color: #e2e8f0; width: 36px; text-align: center; opacity: 1; }
    .detail-label { font-size: 1rem; color: #c9d1d9; margin-bottom: 4px; font-weight: 400; }
    .detail-value { font-weight: 600; color: #ffffff; }
    .arrow { color: #94a3b8; font-size: 1.6rem; margin-left: auto; }
    .settings-item:hover, .card-item:hover { background: rgba(30, 41, 59, 0.60); }
</style>

<!-- Video background -->
<video class="video-bg" autoplay muted loop playsinline>
    <source src="/grokcamping/assets/videos/camping-bg.mp4" type="video/mp4">
    Your browser does not support the video tag.
</video>
<div class="overlay"></div>

<div class="profile-top">
    <div class="avatar-wrap">
        <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Profile Photo" class="avatar">
        <label for="avatar-upload" class="upload-label">+</label>
        <form id="avatar-form" method="post" enctype="multipart/form-data">
            <input type="file" id="avatar-upload" name="avatar" accept="image/*" onchange="this.form.submit();" hidden>
        </form>
    </div>

<!-- Editable Name -->
<h1 class="name" id="name-display" onclick="editName()"><?php echo htmlspecialchars($username); ?></h1>
<form id="name-form" method="post" style="display:none;">
    <input type="text" name="new_name" id="name-input" class="name-input" value="<?php echo htmlspecialchars($username); ?>">
    <button type="submit" class="save-btn">Save</button>
</form>

<!-- Editable Bio/Status -->
<p class="status" id="status-display" onclick="editStatus()"><?php echo htmlspecialchars($status); ?></p>
<form id="status-form" method="post" style="display:none;">
    <textarea name="new_status" id="status-input" class="status-input"><?php echo htmlspecialchars($status); ?></textarea>
    <button type="submit" class="save-btn">Save</button>
</form>
</div>

<div class="card">
    <div class="card-item">
        <div class="icon">📞</div>
        <div>
            <div class="detail-label">Phone number</div>
            <div class="detail-value"><?php echo htmlspecialchars($phone); ?></div>
        </div>
        <div class="arrow">›</div>
    </div>
</div>

<div class="card">
    <div class="settings-item"><div class="icon">👤</div><div>Account</div><div class="arrow">›</div></div>
    <div class="settings-item"><div class="icon">🔒</div><div>Privacy</div><div class="arrow">›</div></div>
    <div class="settings-item"><div class="icon">🔔</div><div>Notifications</div><div class="arrow">›</div></div>
    <div class="settings-item"><div class="icon">⭐</div><div>Favourites</div><div class="arrow">›</div></div>
    <div class="settings-item"><div class="icon">?</div><div>Help</div><div class="arrow">›</div></div>
</div>

<?php if (isset($upload_error)): ?>
    <p style="color:#ff6b6b; text-align:center; margin:20px;"><?php echo $upload_error; ?></p>
<?php endif; ?>

<script>
function editName() {
    document.getElementById('name-display').style.display = 'none';
    document.getElementById('name-form').style.display = 'block';
    document.getElementById('name-input').focus();
    document.getElementById('name-input').select();
}

function editStatus() {
    document.getElementById('status-display').style.display = 'none';
    document.getElementById('status-form').style.display = 'block';
    document.getElementById('status-input').focus();
    document.getElementById('status-input').select();
}
</script>

<?php
// Footer or close
if (file_exists('../includes/footer.php')) {
    include '../includes/footer.php';
} else {
    ?>
    </body>
    </html>
    <?php
}
?>