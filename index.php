<?php
$pageTitle = 'Product Catalog';
include 'header.php';
$conn = new mysqli('localhost', 'root', '', 'shop_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, name, description, price, image 
        FROM products 
        ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<div class="product-list">
  <?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="product-card">
        <?php if (!empty($row['image'])): ?>
          <img 
            src="<?= htmlspecialchars($row['image']) ?>" 
            alt="<?= htmlspecialchars($row['name']) ?>" 
            style="max-width:100%; margin-bottom:10px; border-radius:4px;"
          >
        <?php endif; ?>

        <h2><?= htmlspecialchars($row['name']) ?></h2>
        <p><?= nl2br(htmlspecialchars($row['description'])) ?></p>
        <div class="price">$<?= number_format($row['price'], 2) ?></div>

        <div class="admin-actions">
          <a href="edit_product.php?id=<?= $row['id'] ?>">Edit</a> |
          <a 
            href="delete_product.php?id=<?= $row['id'] ?>" 
            onclick="return confirm('Delete this product?');"
          >Delete</a>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No products available.</p>
  <?php endif; ?>
</div>

<?php
$conn->close();
include 'footer.php';
