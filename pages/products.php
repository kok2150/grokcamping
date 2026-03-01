<?php
// pages/products.php - Product Listing Page for Grok Camping

session_start();

// Include database connection (optional for now, can be removed if not used yet)
include '../includes/db_connect.php';

// Page title
$page_title = "Products - Grok Camping";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f8f9fa; /* Light white-gray, clean outdoor feel */
            color: #212529;
        }
        .container {
            max-width: 1300px;
            margin: 60px auto;
            padding: 0 20px;
        }
        h1 {
            text-align: center;
            color: #0d6efd; /* Blue accent for title */
            margin-bottom: 60px;
            font-size: 3rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }
        .product-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(14px); /* Frosted glass effect */
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
            transition: all 0.35s ease;
            border: 1px solid rgba(13, 110, 253, 0.08);
        }
        .product-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 25px 50px rgba(13, 110, 253, 0.18);
        }
        .product-image {
            width: 100%;
            height: 300px;
            object-fit: contain;
            padding: 30px;
            background: #ffffff;
            border-bottom: 1px solid #e9ecef;
        }
        .product-info {
            padding: 25px;
            text-align: center;
        }
        .product-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 12px;
            color: #212529;
        }
        .product-desc {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 20px;
            height: 70px;
            overflow: hidden;
            line-height: 1.5;
        }
        .product-price {
            font-size: 1.8rem;
            font-weight: bold;
            color: #0d6efd; /* Blue price highlight */
            margin-bottom: 20px;
        }
        .btn-add {
            display: inline-block;
            padding: 12px 40px;
            background: #0d6efd;
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
        }
        .btn-add:hover {
            background: #0b5ed7;
            transform: scale(1.06);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.3);
        }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="container">
    <h1>Explore Our Camping Gear</h1>

    <div class="product-grid">
        <!-- Product 1 -->
        <div class="product-card">
            <img src="../assets/images/tent.jpg" alt="4-Season Tent" class="product-image">
            <div class="product-info">
                <div class="product-name">4-Season Dome Tent (4 Person)</div>
                <div class="product-desc">Waterproof, windproof, easy setup. Perfect for all-weather camping.</div>
                <div class="product-price">$189.00</div>
                <a href="#" class="btn-add">Add to Cart</a>
            </div>
        </div>

        <!-- Product 2 -->
        <div class="product-card">
            <img src="../assets/images/sleepingbag.jpg" alt="Mummy Sleeping Bag" class="product-image">
            <div class="product-info">
                <div class="product-name">Down Mummy Sleeping Bag (-10°C)</div>
                <div class="product-desc">800-fill power down, lightweight, compact. Warm and comfortable.</div>
                <div class="product-price">$129.00</div>
                <a href="#" class="btn-add">Add to Cart</a>
            </div>
        </div>

        <!-- Product 3 -->
        <div class="product-card">
            <img src="../assets/images/lantern.jpg" alt="Rechargeable LED Lantern" class="product-image">
            <div class="product-info">
                <div class="product-name">Rechargeable LED Camping Lantern (1000LM)</div>
                <div class="product-desc">USB rechargeable, 4 light modes, 360° lighting. Long battery life.</div>
                <div class="product-price">$49.90</div>
                <a href="#" class="btn-add">Add to Cart</a>
            </div>
        </div>

        <!-- Product 4 -->
        <div class="product-card">
            <img src="../assets/images/stove.jpg" alt="Portable Gas Stove" class="product-image">
            <div class="product-info">
                <div class="product-name">Portable Gas Camping Stove</div>
                <div class="product-desc">Compact, wind-resistant, high BTU output. Perfect for outdoor cooking.</div>
                <div class="product-price">$69.90</div>
                <a href="#" class="btn-add">Add to Cart</a>
            </div>
        </div>

        <!-- Product 5 -->
        <div class="product-card">
            <img src="../assets/images/picnicmat.jpg" alt="Waterproof Picnic Mat" class="product-image">
            <div class="product-info">
                <div class="product-name">Large Waterproof Picnic Mat (300x300cm)</div>
                <div class="product-desc">Foldable, sand-resistant, easy clean. Great for family outings.</div>
                <div class="product-price">$39.90</div>
                <a href="#" class="btn-add">Add to Cart</a>
            </div>
        </div>

        <!-- Product 6 -->
        <div class="product-card">
            <img src="../assets/images/powerbank.jpg" alt="Solar Power Bank" class="product-image">
            <div class="product-info">
                <div class="product-name">Solar Power Bank (20000mAh)</div>
                <div class="product-desc">Built-in solar panel, fast charging, IP65 waterproof. Ideal for off-grid camping.</div>
                <div class="product-price">$89.90</div>
                <a href="#" class="btn-add">Add to Cart</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>