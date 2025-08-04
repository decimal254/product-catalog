<?php
session_start();
$pageTitle = 'Edit Product';
include 'header.php';

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=shop_db;charset=utf8mb4',
        'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}


if (empty($_GET['id'])) {
    die("Product ID is missing.");
}
$id = (int)$_GET['id'];

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    die("Product not found.");
}


$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);

    if ($name === '' || $price === false || $price < 0) {
        $message = '<span class="error">Invalid name or price.</span>';
    } else {
       
        $image = $product['image'];
        if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            if (in_array($ext, $allowed, true)) {
                if (!is_dir('uploads')) mkdir('uploads', 0755, true);
                $newPath = 'uploads/' . uniqid('', true) . ".$ext";
                move_uploaded_file($_FILES['image']['tmp_name'], $newPath);
                $image = $newPath;
            }
        }

        $stmt = $pdo->prepare(
            'UPDATE products SET name = ?, description = ?, price = ?, image = ? WHERE id = ?'
        );
        $stmt->execute([$name, $description, $price, $image, $id]);
        header('Location: index.php');
        exit;
    }
} else {
    
    $name = $product['name'];
    $description = $product['description'];
    $price = $product['price'];
    $image = $product['image'];
}
?>

<main>
  <h2>Edit Product</h2>
  <?php if ($message): ?>
    <p><?= $message ?></p>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <?php if ($image): ?>
      <img src="<?= htmlspecialchars($image) ?>" style="max-width:150px;margin-bottom:10px;"><br>
    <?php endif; ?>
    <div class="form-group">
      <label for="image">Change Image:</label>
      <input type="file" name="image" id="image">
    </div>
    <div class="form-group">
      <label for="name">Name:</label>
      <input required id="name" name="name" value="<?= htmlspecialchars($name) ?>">
    </div>
    <div class="form-group">
      <label for="description">Description:</label>
      <textarea id="description" name="description"><?= htmlspecialchars($description) ?></textarea>
    </div>
    <div class="form-group">
      <label for="price">Price (USD):</label>
      <input required type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($price) ?>">
    </div>
    <div class="form-group">
      <button type="submit">Update Product</button>
    </div>
  </form>
  <a href="index.php" class="back-link">← Back to Catalog</a>
</main>

<?php include 'footer.php'; ?>
