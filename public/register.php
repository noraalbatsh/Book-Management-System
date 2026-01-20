<?php
require_once(realpath(dirname(__FILE__)) . '/../includes/bootstrap.php');

if (current_user()) {
    header('Location: ' . app_base_url() . '/public/dashboard.php');
    exit;
}

$al = new AlFacade();
$message = null;
$type = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $res = $al->registerMember($name, $email, $password);
    if ($res['ok']) {
        $message = $res['message'] ?? 'Registration successful.';
        $type = 'ok';
    } else {
        $message = $res['message'] ?? 'Registration failed.';
        $type = 'err';
    }
}

$title = 'Register';
require_once(realpath(dirname(__FILE__)) . '/../includes/header.php');
?>

<div class="grid">
  <div class="col-6">
    <div class="card">
      <h1 class="h1">Register</h1>
      <p class="small">Create a new member account.</p>

      <?php if ($message): ?>
        <div class="alert <?php echo $type; ?>"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>

      <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />

        <label class="label">Name</label>
        <input type="text" name="name" required />

        <label class="label" style="margin-top:12px;">Email</label>
        <input type="email" name="email" required />

        <label class="label" style="margin-top:12px;">Password</label>
        <input type="password" name="password" required />

        <div style="margin-top:14px; display:flex; gap:10px; align-items:center;">
          <button class="btn" type="submit">Create account</button>
<a class="btn btn-outline" href="login.php">Back to login</a>
        </div>
      </form>
    </div>
  </div>

  <div class="col-6">
    <div class="card">
      <h2 class="h2">Password tips</h2>
      <ul>
        <li>Use at least 6 characters</li>
        <li>Mix letters and numbers if possible</li>
      </ul>
    </div>
  </div>
</div>


