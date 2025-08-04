<?php
session_start();
$pageTitle = 'Add Product';
include 'header.php';

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=shop_db;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

$message = '';
$name = $description = $price = '';
$imagePath = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);

    if ($name === '') {
        $message = '<span class="error">Product name is required.</span>';
    } elseif ($price === false || $price < 0) {
        $message = '<span class="error">Please enter a valid price.</span>';
    } else {
        
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($ext, $allowed, true)) {
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0755, true);
                }
                $imagePath = 'uploads/' . uniqid('', true) . ".$ext";
                move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
            } else {
                $message .= '<br><span class="error">Only JPG/PNG/GIF images are allowed.</span>';
            }
        }

        
        $stmt = $pdo->prepare(
            'INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$name, $description, $price, $imagePath]);
        $message = '<span class="message">Product added successfully!</span>';
        $name = $description = $price = '';
        $imagePath = null;
    }
}
?>

<main>
  <h2>Add New Product</h2>

  <?php if ($message): ?>
    <p><?= $message ?></p>
  <?php endif; ?>

  <form method="post" action="" enctype="multipart/form-data" id="productForm">
    <div class="form-group">
      <label for="image">Product Image:</label>
      <input type="file" id="image" name="image" accept="image/*">
    </div>

    <div class="form-group">
      <label for="name">Name:</label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
    </div>

    <div class="form-group">
      <label for="description">Description:</label>
      <textarea id="description" name="description" rows="4"><?= htmlspecialchars($description) ?></textarea>
    </div>

    <div class="form-group">
      <label for="price">Price (USD):</label>
      <input type="number" id="price" name="price" value="<?= htmlspecialchars($price) ?>" step="0.01" required>
    </div>

    <div class="form-group">
      <button type="submit">Add Product</button>
    </div>
  </form>

  <a class="back-link" href="index.php">← Back to Catalog</a>
</main>

<script>
document.getElementById('productForm').addEventListener('submit', function(e) {
  const name = this.name.value.trim();
  const price = parseFloat(this.price.value);
  let msgs = [];

  if (!name) msgs.push('Name is required');
  if (isNaN(price) || price < 0) msgs.push('Price must be a positive number');

  if (msgs.length) {
    alert(msgs.join("\n"));
    e.preventDefault();
  }
});
</script>

<?php include 'footer.php'; ?>
