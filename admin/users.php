<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

if (isset($_GET['toggle'])) {
    $userId = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE users SET status = IF(status='active','inactive','active') WHERE user_id = ? AND role = 'customer'");
    $stmt->execute([$userId]);
    redirect('admin/users.php');
}

$customers = $pdo->query("SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Manage Customers';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-3"><i class="fa-solid fa-users"></i> Manage Customers</h4>

  <div class="table-responsive">
    <table class="table align-middle bg-white">
      <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Joined</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($customers as $c): ?>
          <tr>
            <td><?php echo e($c['full_name']); ?></td>
            <td><?php echo e($c['email']); ?></td>
            <td><?php echo e($c['phone']); ?></td>
            <td><?php echo date('M d, Y', strtotime($c['created_at'])); ?></td>
            <td><span class="badge bg-<?php echo $c['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($c['status']); ?></span></td>
            <td><a href="?toggle=<?php echo $c['user_id']; ?>" class="btn btn-sm btn-outline-secondary"><?php echo $c['status'] === 'active' ? 'Deactivate' : 'Activate'; ?></a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
