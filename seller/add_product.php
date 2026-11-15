<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireSeller();
$sellerId = currentUserId();

$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();

$errors = [];
$old = ['product_name' => '', 'category_id' => '', 'description' => '', 'price' => '', 'stock' => ''];
$specNames = $_POST['spec_name'] ?? [];
$specValues = $_POST['spec_value'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['product_name'] = trim($_POST['product_name'] ?? '');
    $old['category_id'] = (int)($_POST['category_id'] ?? 0);
    $old['description'] = trim($_POST['description'] ?? '');
    $old['price'] = $_POST['price'] ?? '';
    $old['stock'] = $_POST['stock'] ?? '';

    if ($old['product_name'] === '') $errors[] = 'Product name is required.';
    if ($old['category_id'] <= 0) $errors[] = 'Please select a category.';
    if ($old['description'] === '') $errors[] = 'Description is required.';
    if (!is_numeric($old['price']) || $old['price'] <= 0) $errors[] = 'Please enter a valid price.';
    if (!is_numeric($old['stock']) || $old['stock'] < 0) $errors[] = 'Please enter a valid stock quantity.';

    $imageName = null;
    if (empty($errors)) {
        try {
            $imageName = handleProductImageUpload('product_image');
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO products (seller_id, category_id, product_name, description, price, stock, image, status)
                                VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$sellerId, $old['category_id'], $old['product_name'], $old['description'], $old['price'], $old['stock'], $imageName]);
        $productId = $pdo->lastInsertId();

        foreach ($specNames as $i => $name) {
            $name = trim($name);
            $value = trim($specValues[$i] ?? '');
            if ($name !== '' && $value !== '') {
                $specStmt = $pdo->prepare("INSERT INTO product_specifications (product_id, specification_name, specification_value) VALUES (?, ?, ?)");
                $specStmt->execute([$productId, $name, $value]);
            }
        }

        flash('success', 'Product added successfully.');
        redirect('seller/products.php');
    }
}

$pageTitle = 'Add Product';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-3"><i class="fa-solid fa-plus"></i> Add New Product</h4>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?php echo e($err); ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="card p-4">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Product Name</label>
        <input type="text" name="product_name" class="form-control" required value="<?php echo e($old['product_name']); ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-select" required>
          <option value="">-- Select Category --</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['category_id']; ?>" <?php echo $old['category_id'] == $cat['category_id'] ? 'selected' : ''; ?>><?php echo e($cat['category_name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3" required><?php echo e($old['description']); ?></textarea>
      </div>
      <div class="col-md-4">
        <label class="form-label">Price (Rs.)</label>
        <input type="number" step="0.01" name="price" class="form-control" required value="<?php echo e($old['price']); ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Stock Quantity</label>
        <input type="number" name="stock" class="form-control" required value="<?php echo e($old['stock']); ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Product Image</label>
        <input type="file" name="product_image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
      </div>

      <div class="col-12">
        <hr>
        <label class="form-label">Specifications <span class="text-muted small">(optional, e.g. RAM, Storage, Battery)</span></label>
        <div id="specRows">
          <div class="row g-2 mb-2">
            <div class="col-5"><input type="text" name="spec_name[]" class="form-control form-control-sm" placeholder="Specification name"></div>
            <div class="col-5"><input type="text" name="spec_value[]" class="form-control form-control-sm" placeholder="Value"></div>
          </div>
          <div class="row g-2 mb-2">
            <div class="col-5"><input type="text" name="spec_name[]" class="form-control form-control-sm" placeholder="Specification name"></div>
            <div class="col-5"><input type="text" name="spec_value[]" class="form-control form-control-sm" placeholder="Value"></div>
          </div>
          <div class="row g-2 mb-2">
            <div class="col-5"><input type="text" name="spec_name[]" class="form-control form-control-sm" placeholder="Specification name"></div>
            <div class="col-5"><input type="text" name="spec_value[]" class="form-control form-control-sm" placeholder="Value"></div>
          </div>
          <div class="row g-2 mb-2">
            <div class="col-5"><input type="text" name="spec_name[]" class="form-control form-control-sm" placeholder="Specification name"></div>
            <div class="col-5"><input type="text" name="spec_value[]" class="form-control form-control-sm" placeholder="Value"></div>
          </div>
        </div>
      </div>
    </div>
    <button type="submit" class="btn btn-brand mt-3">Save Product</button>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
