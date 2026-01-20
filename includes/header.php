<?php
 
$title = $title ?? 'Library System';
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo htmlspecialchars($title); ?></title>

  <!-- CSS -->
  <link rel="stylesheet" href="assets/style.css?v=<?php echo time(); ?>" />

 
</head>
<body>
  <header class="topbar">
    <div class="container row between center">
 
      <a class="brand" href="dashboard.php">Library</a>
 
      <nav class="nav">
        <?php if ($user): ?>
          <span class="nav-user">Hi, <?php echo htmlspecialchars($user['name']); ?></span>
          <a class="btn btn-outline" href="logout.php">Logout</a>
        <?php else: ?>
          <a class="btn btn-outline" href="login.php">Login</a>
          <a class="btn" href="register.php">Register</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main class="container">
