<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$email = '';
$successMsg = flash('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Invalid email or password.';
        } elseif ($user['status'] !== 'active') {
            $errors[] = 'Your account is inactive. Please contact the administrator.';
        } else {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') redirect('admin/dashboard.php');
            elseif ($user['role'] === 'seller') redirect('seller/dashboard.php');
            else redirect('customer/dashboard.php');
        }
    }
}

$pageTitle = 'Login';
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="card auth-card shadow-sm">
    <div class="card-body p-4">
      <h3 class="text-center mb-3"><i class="fa-solid fa-right-to-bracket"></i> Login to SmartCart</h3>

      <?php if ($successMsg): ?><div class="alert alert-success auto-dismiss"><?php echo e($successMsg); ?></div><?php endif; ?>
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?php echo e($err); ?></li><?php endforeach; ?></ul>
        </div>
      <?php endif; ?>

      <form method="post">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required value="<?php echo e($email); ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-brand w-100">Login</button>
      </form>
      <p class="text-center mt-3 mb-0">Don't have an account? <a href="<?php echo BASE_URL; ?>auth/register.php">Register here</a></p>

      <hr>
      <p class="small text-muted mb-1">Demo accounts (password: <strong>password</strong>):</p>
      <ul class="small text-muted mb-0">
        <li>Admin: admin@smartcart.com</li>
        <li>Seller: raj.seller@smartcart.com</li>
        <li>Customer: sita@smartcart.com</li>
      </ul>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
