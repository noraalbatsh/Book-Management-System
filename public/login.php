<?php
require_once(realpath(dirname(__FILE__)) . '/../includes/bootstrap.php');
 
if (current_user()) {
    header('Location: dashboard.php');
    exit;
}

$al = new AlFacade();
$message = null;
$type = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $res = $al->login($email, $password);

    if ($res['ok']) {
        $_SESSION['user'] = $res['user'];
 
        header('Location: dashboard.php');
        exit;
    } else {
        $message = $res['message'] ?? 'Login failed.';
        $type = 'err';
    }
}

$title = 'Login';
require_once(realpath(dirname(__FILE__)) . '/../includes/header.php');
?>

<div class="grid">
  <div class="col-6">
    <div class="card">
      <h1 class="h1">Login</h1>
      <p class="small">Use your email and password to enter the library system.</p>

      <?php if ($message): ?>
        <div class="alert <?php echo $type; ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />

        <label class="label">Email</label>
        <input type="email" name="email" required />

        <label class="label" style="margin-top:12px;">Password</label>
        <input type="password" name="password" required />

        <div style="margin-top:14px; display:flex; gap:10px; align-items:center;">
          <button class="btn" type="submit">Login</button>
          <a class="btn btn-outline" href="register.php">Create account</a>
        </div>
      </form>
    </div>
  </div>

  <div class="col-6">
   <div class="card" style="padding:40px;">
  <h2 class="h2" style="margin-bottom:15px;">What you can do</h2>
  <ul style="margin-left:20px; line-height:1.8;">
    <li>Search for books</li>
    <li>Borrow and return books</li>
    <li>See your borrowing history</li>
  </ul>
</div>


 
