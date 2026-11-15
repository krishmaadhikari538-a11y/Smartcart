<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$old = ['full_name' => '', 'email' => '', 'phone' => '', 'address' => '', 'role' => 'customer'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['phone'] = trim($_POST['phone'] ?? '');
    $old['address'] = trim($_POST['address'] ?? '');
    $old['role'] = in_array($_POST['role'] ?? '', ['customer', 'seller']) ? $_POST['role'] : 'customer';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($old['full_name'] === '') $errors[] = 'Full name is required.';
    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if ($old['phone'] === '') $errors[] = 'Phone number is required.';
    if ($old['address'] === '') $errors[] = 'Address is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) {
            $errors[] = 'This email is already registered. Please login instead.';
        }
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, phone, address, role, status)
                                VALUES (?, ?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$old['full_name'], $old['email'], $hashed, $old['phone'], $old['address'], $old['role']]);

        flash('success', 'Registration successful! You can now log in.');
        redirect('auth/login.php');
    }
}

$pageTitle = 'Register';
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="card auth-card shadow-sm">
    <div class="card-body p-4">
      <h3 class="text-center mb-3"><i class="fa-solid fa-user-plus"></i> Create your SmartCart Account</h3>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($errors as $err): ?><li><?php echo e($err); ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" class="needs-validation" novalidate>
        <div class="mb-3">
          <label class="form-label">I want to register as</label>
          <select name="role" class="form-select">
            <option value="customer" <?php echo $old['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
            <option value="seller" <?php echo $old['role'] === 'seller' ? 'selected' : ''; ?>>Seller</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" required value="<?php echo e($old['full_name']); ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required value="<?php echo e($old['email']); ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control" required value="<?php echo e($old['phone']); ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Address</label>
          <input type="text" name="address" class="form-control" required value="<?php echo e($old['address']); ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" minlength="6" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control" minlength="6" required>
        </div>
        <button type="submit" class="btn btn-brand w-100">Register</button>
      </form>
      <p class="text-center mt-3 mb-0">Already have an account? <a href="<?php echo BASE_URL; ?>auth/login.php">Login here</a></p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
