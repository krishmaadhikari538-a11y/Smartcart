<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

if (isset($_GET['toggle'])) {
    $userId = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE users SET status = IF(status='active','inactive','active') WHERE user_id = ? AND role = 'seller'");
    $stmt->execute([$userId]);
    redirect('admin/sellers.php');
}

$sellers = $pdo->query("SELECT s.*, (SELECT COUNT(*) FROM products p WHERE p.seller_id = s.user_id) AS product_count
                         FROM users s WHERE s.role = 'seller' ORDER BY s.created_at DESC")->fetchAll();

$pageTitle = 'Manage Sellers';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-3"><i class="fa-solid fa-store"></i> Manage Sellers</h4>

  <div class="table-responsive">
    <table class="table align-middle bg-white">
      <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Products</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($sellers as $s): ?>
          <tr>
            <td><?php echo e($s['full_name']); ?></td>
            <td><?php echo e($s['email']); ?></td>
            <td><?php echo e($s['phone']); ?></td>
            <td><?php echo $s['product_count']; ?></td>
            <td><span class="badge bg-<?php echo $s['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($s['status']); ?></span></td>
            <td><a href="?toggle=<?php echo $s['user_id']; ?>" class="btn btn-sm btn-outline-secondary"><?php echo $s['status'] === 'active' ? 'Deactivate' : 'Approve/Activate'; ?></a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
