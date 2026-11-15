<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireCustomer();
$userId = currentUserId();

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$errors = [];
$successMsg = flash('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';

    if ($fullName === '') $errors[] = 'Full name is required.';
    if ($phone === '') $errors[] = 'Phone number is required.';
    if ($address === '') $errors[] = 'Address is required.';
    if ($newPassword !== '' && strlen($newPassword) < 6) $errors[] = 'New password must be at least 6 characters.';

    if (empty($errors)) {
        if ($newPassword !== '') {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET full_name=?, phone=?, address=?, password=? WHERE user_id=?");
            $update->execute([$fullName, $phone, $address, $hashed, $userId]);
        } else {
            $update = $pdo->prepare("UPDATE users SET full_name=?, phone=?, address=? WHERE user_id=?");
            $update->execute([$fullName, $phone, $address, $userId]);
        }
        $_SESSION['full_name'] = $fullName;
        flash('success', 'Profile updated successfully.');
        redirect('customer/profile.php');
    }
}

$pageTitle = 'My Profile';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <div class="card mx-auto p-4" style="max-width:600px;">
    <h5 class="mb-3"><i class="fa-solid fa-user"></i> My Profile</h5>

    <?php if ($successMsg): ?><div class="alert alert-success auto-dismiss"><?php echo e($successMsg); ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?php echo e($err); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" value="<?php echo e($user['full_name']); ?>" required></div>
      <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" value="<?php echo e($user['email']); ?>" disabled></div>
      <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?php echo e($user['phone']); ?>" required></div>
      <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control" required><?php echo e($user['address']); ?></textarea></div>
      <div class="mb-3"><label class="form-label">New Password <span class="text-muted small">(leave blank to keep current password)</span></label><input type="password" name="new_password" class="form-control" minlength="6"></div>
      <button type="submit" class="btn btn-brand w-100">Update Profile</button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
