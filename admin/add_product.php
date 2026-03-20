<?php
session_start();
require_once '../includes/db_connect.php'; 

// 提示信息变量
$message = "";

// 1. 从数据库动态获取已有的分类和品牌，用于渲染下拉列表
try {
    // 获取不重复的分类
    $cat_query = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''");
    $categories = $cat_query->fetchAll(PDO::FETCH_COLUMN);

    // 获取不重复的品牌
    $brand_query = $pdo->query("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != ''");
    $brands = $brand_query->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = [];
    $brands = [];
}

// 2. 处理表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 处理分类：如果选择了 NEW_ITEM，则取新输入框的值
    $category = ($_POST['category'] == 'NEW_ITEM') ? trim($_POST['new_category']) : $_POST['category'];
    // 处理品牌：如果选择了 NEW_ITEM，则取新输入框的值
    $brand = ($_POST['brand'] == 'NEW_ITEM') ? trim($_POST['new_brand']) : $_POST['brand'];
    
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    // 3. 处理图片上传
    $target_dir = "../assets/images/products/";
    // 确保目录存在，且赋予写入权限
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
    $new_file_name = time() . "_" . uniqid() . "." . $file_extension;
    $target_file = $target_dir . $new_file_name;

    // 只有图片上传成功才写入数据库
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // 这里必须确保你的数据库里有 description 列，如果没有，请执行：
        // ALTER TABLE products ADD COLUMN description TEXT AFTER price;
        $db_image_path = "../assets/images/products/" . $new_file_name;
        
        $sql = "INSERT INTO products (name, brand, category, price, description, image_url) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$name, $brand, $category, $price, $description, $db_image_path])) {
            $message = "<div class='alert success'>✅ Product added successfully! Brand: $brand</div>";
            // 2秒后重定向或刷新，更新下拉列表
            header("Refresh:2"); 
        } else {
            $message = "<div class='alert error'>❌ Database Error: Could not save product.</div>";
        }
    } else {
        $message = "<div class='alert error'>❌ Upload Error: Check folder permissions.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - GROK CAMPING Admin</title>
    <style>
        :root { --accent: #daf63b; --bg: #000; --card: #111; --text: #eee; --border: #333; }
        body { background: var(--bg); color: var(--text); font-family: 'Helvetica Neue', Arial, sans-serif; padding: 40px; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; background: var(--card); padding: 30px; border: 1px solid var(--border); border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h2 { border-bottom: 2px solid var(--accent); padding-bottom: 10px; margin-bottom: 25px; letter-spacing: 1px; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-size: 0.85rem; color: #aaa; text-transform: uppercase; }
        input, select, textarea { width: 100%; padding: 12px; background: #1a1a1a; border: 1px solid var(--border); color: #fff; border-radius: 8px; font-size: 1rem; }
        input:focus, select:focus, textarea:focus { border-color: var(--accent); outline: none; background: #222; }
        
        /* 隐藏的输入框样式 */
        .hidden-input { display: none; margin-top: 12px; padding: 15px; border-left: 3px solid var(--accent); background: #1a1a1a; border-radius: 0 8px 8px 0; }
        
        button { width: 100%; padding: 15px; background: var(--accent); color: #000; border: none; font-weight: 800; border-radius: 30px; cursor: pointer; font-size: 1rem; text-transform: uppercase; transition: 0.3s; margin-top: 10px; }
        button:hover { background: #fff; transform: translateY(-2px); }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
        .success { background: rgba(218, 246, 59, 0.1); border: 1px solid var(--accent); color: var(--accent); }
        .error { background: rgba(255, 0, 0, 0.1); border: 1px solid #ff4444; color: #ff4444; }
        
        .nav-links { margin-top: 25px; text-align: center; border-top: 1px solid var(--border); padding-top: 20px; }
        .nav-links a { color: #888; text-decoration: none; font-size: 0.85rem; margin: 0 15px; transition: 0.2s; }
        .nav-links a:hover { color: var(--accent); }
    </style>
</head>
<body>

<div class="container">
    <h2>ADD NEW CAMPING GEAR</h2>
    
    <?= $message ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="name" placeholder="e.g. 4-Season Family Tent" required>
        </div>

        <div class="form-group">
            <label>Brand (品牌)</label>
            <select name="brand" id="brandSelect" onchange="toggleNewInput('brandSelect', 'newBrandInput')" required>
                <option value="" disabled selected>-- Select a Brand --</option>
                <?php foreach($brands as $b): ?>
                    <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
                <?php endforeach; ?>
                <option value="NEW_ITEM" style="color: var(--accent); font-weight:bold;">+ Add New Brand...</option>
            </select>
            <div id="newBrandInput" class="hidden-input">
                <label>New Brand Name</label>
                <input type="text" name="new_brand" placeholder="Enter new brand name">
            </div>
        </div>

        <div class="form-group">
            <label>Category (种类)</label>
            <select name="category" id="catSelect" onchange="toggleNewInput('catSelect', 'newCatInput')" required>
                <option value="" disabled selected>-- Select a Category --</option>
                <?php foreach($categories as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                <?php endforeach; ?>
                <option value="NEW_ITEM" style="color: var(--accent); font-weight:bold;">+ Add New Category...</option>
            </select>
            <div id="newCatInput" class="hidden-input">
                <label>New Category Name</label>
                <input type="text" name="new_category" placeholder="e.g. Sleeping Bags">
            </div>
        </div>

        <div class="form-group">
            <label>Price ($)</label>
            <input type="number" step="0.01" name="price" placeholder="0.00" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="4" placeholder="Describe the gear features..."></textarea>
        </div>

        <div class="form-group">
            <label>Product Image</label>
            <input type="file" name="image" accept="image/*" required>
            <small style="color: #666; font-size: 0.75rem;">Recommended size: 800x800px</small>
        </div>

        <button type="submit">UPLOAD TO STORE</button>
    </form>

    <div class="nav-links">
        <a href="dashboard.php">← Admin Dashboard</a>
        <a href="../pages/products.php">Visit Shop</a>
    </div>
</div>

<script>
/**
 * 切换显示手动输入框的逻辑
 * @param {string} selectId 下拉框ID
 * @param {string} inputId  要显示的Div ID
 */
function toggleNewInput(selectId, inputId) {
    var select = document.getElementById(selectId);
    var inputDiv = document.getElementById(inputId);
    var inputField = inputDiv.querySelector('input');

    if (select.value === 'NEW_ITEM') {
        inputDiv.style.display = 'block';
        inputField.setAttribute('required', 'true'); // 必填
        inputField.focus(); // 自动聚焦
    } else {
        inputDiv.style.display = 'none';
        inputField.removeAttribute('required'); // 取消必填
    }
}
</script>

</body>
</html>