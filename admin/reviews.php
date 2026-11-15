<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

if (isset($_GET['hide'])) {
    $stmt = $pdo->prepare("UPDATE reviews SET status = 'hidden' WHERE review_id = ?");
    $stmt->execute([(int)$_GET['hide']]);
    flash('success', 'Review hidden.');
    redirect('admin/reviews.php');
}

if (isset($_GET['show'])) {
    $stmt = $pdo->prepare("UPDATE reviews SET status = 'visible' WHERE review_id = ?");
    $stmt->execute([(int)$_GET['show']]);
    flash('success', 'Review restored.');
    redirect('admin/reviews.php');
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE review_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    flash('success', 'Review deleted.');
    redirect('admin/reviews.php');
}

$reviews = $pdo->query("SELECT r.*, u.full_name, p.product_name FROM reviews r
                         JOIN users u ON r.user_id = u.user_id
                         JOIN products p ON r.product_id = p.product_id
                         ORDER BY r.created_at DESC")->fetchAll();

$successMsg = flash('success');

$pageTitle = 'Manage Reviews';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-3"><i class="fa-solid fa-star"></i> Manage Reviews</h4>
  <?php if ($successMsg): ?><div class="alert alert-success auto-dismiss"><?php echo e($successMsg); ?></div><?php endif; ?>

  <?php if (empty($reviews)): ?>
    <div class="alert alert-info">No reviews yet.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle bg-white">
        <thead><tr><th>Product</th><th>Customer</th><th>Rating</th><th>Comment</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($reviews as $r): ?>
            <tr>
              <td><?php echo e($r['product_name']); ?></td>
              <td><?php echo e($r['full_name']); ?></td>
              <td><?php echo str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']); ?></td>
              <td class="small"><?php echo e($r['comment']); ?></td>
              <td><span class="badge bg-<?php echo $r['status'] === 'visible' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($r['status']); ?></span></td>
              <td class="text-nowrap">
                <?php if ($r['status'] === 'visible'): ?>
                  <a href="?hide=<?php echo $r['review_id']; ?>" class="btn btn-sm btn-outline-secondary">Hide</a>
                <?php else: ?>
                  <a href="?show=<?php echo $r['review_id']; ?>" class="btn btn-sm btn-outline-success">Show</a>
                <?php endif; ?>
                <a href="?delete=<?php echo $r['review_id']; ?>" class="btn btn-sm btn-outline-danger confirm-delete">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
