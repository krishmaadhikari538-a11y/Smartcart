<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireCustomer();
$userId = currentUserId();

// Mark a single notification as read
if (isset($_GET['mark_read'])) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
    $stmt->execute([(int)$_GET['mark_read'], $userId]);
    redirect('customer/notifications.php');
}

// Mark all as read
if (isset($_GET['mark_all_read'])) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->execute([$userId]);
    redirect('customer/notifications.php');
}

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

$pageTitle = 'Notifications';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa-solid fa-bell"></i> Notifications</h4>
    <?php if (!empty($notifications)): ?>
      <a href="?mark_all_read=1" class="btn btn-sm btn-outline-brand">Mark all as read</a>
    <?php endif; ?>
  </div>

  <?php if (empty($notifications)): ?>
    <div class="alert alert-info">You have no notifications yet.</div>
  <?php else: ?>
    <?php foreach ($notifications as $n): ?>
      <div class="notification-item p-3 mb-2 rounded bg-white <?php echo !$n['is_read'] ? 'unread' : ''; ?>">
        <div class="d-flex justify-content-between">
          <strong><?php echo e($n['title']); ?></strong>
          <span class="text-muted small"><?php echo date('M d, Y H:i', strtotime($n['created_at'])); ?></span>
        </div>
        <p class="mb-1"><?php echo e($n['message']); ?></p>
        <div class="d-flex gap-2">
          <?php if ($n['related_product_id']): ?>
            <a href="<?php echo BASE_URL; ?>products/product_details.php?id=<?php echo $n['related_product_id']; ?>" class="small">View Product</a>
          <?php endif; ?>
          <?php if (!$n['is_read']): ?>
            <a href="?mark_read=<?php echo $n['notification_id']; ?>" class="small">Mark as read</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
