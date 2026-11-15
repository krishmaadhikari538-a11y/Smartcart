<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../alerts/check_alerts.php';

requireSeller();
$sellerId = currentUserId();

$productId = (int)($_GET['id'] ?? $_POST['product_id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ? AND seller_id = ?");
$stmt->execute([$productId, $sellerId]);
$product = $stmt->fetch();

if (!$product) {
    flash('error', 'Product not found or you do not have permission to edit it.');
    redirect('seller/products.php');
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();
$specs = getProductSpecifications($pdo, $productId);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = trim($_POST['product_name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $specNames = $_POST['spec_name'] ?? [];
    $specValues = $_POST['spec_value'] ?? [];

    if ($productName === '') $errors[] = 'Product name is required.';
    if ($categoryId <= 0) $errors[] = 'Please select a category.';
    if ($description === '') $errors[] = 'Description is required.';
    if (!is_numeric($price) || $price <= 0) $errors[] = 'Please enter a valid price.';
    if (!is_numeric($stock) || $stock < 0) $errors[] = 'Please enter a valid stock quantity.';

    $newImage = $product['image'];
    if (empty($errors)) {
        try {
            $uploaded = handleProductImageUpload('product_image');
            if ($uploaded) $newImage = $uploaded;
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $oldPrice = (float)$product['price'];
        $newPrice = (float)$price;

        $update = $pdo->prepare("UPDATE products SET category_id=?, product_name=?, description=?, price=?, stock=?, image=? WHERE product_id=?");
        $update->execute([$categoryId, $productName, $description, $newPrice, $stock, $newImage, $productId]);

        // Replace specifications
        $del = $pdo->prepare("DELETE FROM product_specifications WHERE product_id = ?");
        $del->execute([$productId]);
        foreach ($specNames as $i => $name) {
            $name = trim($name);
            $value = trim($specValues[$i] ?? '');
            if ($name !== '' && $value !== '') {
                $specStmt = $pdo->prepare("INSERT INTO product_specifications (product_id, specification_name, specification_value) VALUES (?, ?, ?)");
                $specStmt->execute([$productId, $name, $value]);
            }
        }

        // SMART FEATURE: if the price dropped, check price alerts and notify eligible customers
        if ($newPrice < $oldPrice) {
            triggerPriceAlertCheck($pdo, $productId, $newPrice);
        }

        flash('success', 'Product updated successfully.');
        redirect('seller/products.php');
    }
}

$pageTitle = 'Edit Product';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-3"><i class="fa-solid fa-pen"></i> Edit Product</h4>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?php echo e($err); ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="card p-4">
    <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Product Name</label>
        <input type="text" name="product_name" class="form-control" required value="<?php echo e($product['product_name']); ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-select" required>
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['category_id']; ?>" <?php echo $product['category_id'] == $cat['category_id'] ? 'selected' : ''; ?>><?php echo e($cat['category_name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3" required><?php echo e($product['description']); ?></textarea>
      </div>
      <div class="col-md-4">
        <label class="form-label">Price (Rs.)</label>
        <input type="number" step="0.01" name="price" class="form-control" required value="<?php echo e($product['price']); ?>">
        <div class="form-text">Lowering the price will automatically trigger price-drop notifications for customers with a matching alert.</div>
      </div>
      <div class="col-md-4">
        <label class="form-label">Stock Quantity</label>
        <input type="number" name="stock" class="form-control" required value="<?php echo e($product['stock']); ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Product Image</label>
        <input type="file" name="product_image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
        <img src="<?php echo getProductImageUrl($product['image']); ?>" width="60" class="rounded mt-2">
      </div>

      <div class="col-12">
        <hr>
        <label class="form-label">Specifications</label>
        <div id="specRows">
          <?php
            $rows = max(4, count($specs));
            for ($i = 0; $i < $rows; $i++):
              $name = $specs[$i]['specification_name'] ?? '';
              $value = $specs[$i]['specification_value'] ?? '';
          ?>
            <div class="row g-2 mb-2">
              <div class="col-5"><input type="text" name="spec_name[]" class="form-control form-control-sm" placeholder="Specification name" value="<?php echo e($name); ?>"></div>
              <div class="col-5"><input type="text" name="spec_value[]" class="form-control form-control-sm" placeholder="Value" value="<?php echo e($value); ?>"></div>
            </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
    <button type="submit" class="btn btn-brand mt-3">Update Product</button>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
