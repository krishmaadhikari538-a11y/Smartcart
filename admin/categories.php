<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$errors = [];
$editCategory = null;

// Delete
if (isset($_GET['delete'])) {
    $catId = (int)$_GET['delete'];
    $check = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $check->execute([$catId]);
    if ($check->fetchColumn() > 0) {
        flash('error', 'Cannot delete this category because it has products assigned to it.');
    } else {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$catId]);
        flash('success', 'Category deleted.');
    }
    redirect('admin/categories.php');
}

// Load category to edit
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editCategory = $stmt->fetch();
}

// Add or Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);

    if ($name === '') $errors[] = 'Category name is required.';

    if (empty($errors)) {
        if ($categoryId > 0) {
            $stmt = $pdo->prepare("UPDATE categories SET category_name=?, description=? WHERE category_id=?");
            $stmt->execute([$name, $description, $categoryId]);
            flash('success', 'Category updated.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (category_name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            flash('success', 'Category added.');
        }
        redirect('admin/categories.php');
    }
}

$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.category_id) AS product_count
                            FROM categories c ORDER BY c.category_name")->fetchAll();

$successMsg = flash('success');
$errorMsg = flash('error');

$pageTitle = 'Manage Categories';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-3"><i class="fa-solid fa-tags"></i> Manage Categories</h4>

  <?php if ($successMsg): ?><div class="alert alert-success auto-dismiss"><?php echo e($successMsg); ?></div><?php endif; ?>
  <?php if ($errorMsg): ?><div class="alert alert-danger auto-dismiss"><?php echo e($errorMsg); ?></div><?php endif; ?>
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?php echo e($err); ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-md-4">
      <div class="card p-3">
        <h6><?php echo $editCategory ? 'Edit Category' : 'Add Category'; ?></h6>
        <form method="post">
          <input type="hidden" name="category_id" value="<?php echo $editCategory['category_id'] ?? ''; ?>">
          <div class="mb-2">
            <label class="form-label">Name</label>
            <input type="text" name="category_name" class="form-control" required value="<?php echo e($editCategory['category_name'] ?? ''); ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="2"><?php echo e($editCategory['description'] ?? ''); ?></textarea>
          </div>
          <button type="submit" class="btn btn-brand w-100"><?php echo $editCategory ? 'Update' : 'Add'; ?> Category</button>
          <?php if ($editCategory): ?><a href="<?php echo BASE_URL; ?>admin/categories.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a><?php endif; ?>
        </form>
      </div>
    </div>
    <div class="col-md-8">
      <div class="table-responsive">
        <table class="table align-middle bg-white">
          <thead><tr><th>Name</th><th>Description</th><th>Products</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($categories as $cat): ?>
              <tr>
                <td><?php echo e($cat['category_name']); ?></td>
                <td class="small text-muted"><?php echo e($cat['description']); ?></td>
                <td><?php echo $cat['product_count']; ?></td>
                <td class="text-nowrap">
                  <a href="?edit=<?php echo $cat['category_id']; ?>" class="btn btn-sm btn-outline-brand"><i class="fa-solid fa-pen"></i></a>
                  <a href="?delete=<?php echo $cat['category_id']; ?>" class="btn btn-sm btn-outline-danger confirm-delete"><i class="fa-solid fa-trash"></i></a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
