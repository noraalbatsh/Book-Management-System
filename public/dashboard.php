<?php
require_once(realpath(dirname(__FILE__)) . '/../includes/bootstrap.php');
require_login();

$al = new AlFacade();
$user = current_user();

$flash = null;
$flashType = null;

// Handle borrow/return actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';
    $bookId = (int)($_POST['book_id'] ?? 0);

    if ($bookId > 0) {
        if ($action === 'borrow') {
            $res = $al->borrowBook((int)$user['id'], $bookId);
            $flash = $res['message'] ?? '';
            $flashType = $res['ok'] ? 'ok' : 'err';
        } elseif ($action === 'return') {
            $res = $al->returnBook((int)$user['id'], $bookId);
            $flash = $res['message'] ?? '';
            $flashType = $res['ok'] ? 'ok' : 'err';
        }
    }
}

// Search logic
$q = trim($_GET['q'] ?? '');
$books = ($q !== '') ? $al->searchBooks($q) : $al->listBooks();
$loans = $al->listUserLoans((int)$user['id']);

$title = 'Dashboard';
require_once(realpath(dirname(__FILE__)) . '/../includes/header.php');
?>

<div class="grid">
  <div class="col-12">
    <div class="card">
      <h1 class="h1">Library Dashboard</h1>
      <p class="small">Search for books, borrow, return, and view your history.</p>

      <?php if ($flash): ?>
        <div class="alert <?php echo htmlspecialchars($flashType); ?>">
          <?php echo htmlspecialchars($flash); ?>
        </div>
      <?php endif; ?>

      <!-- Search Form -->
      <form method="get" action="" class="row" style="margin-top:10px; align-items:end;">
        <div style="flex:1;">
          <label class="label">Search (title, author, ISBN)</label>
          <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="e.g., Clean Code" />
        </div>
        <div>
          <button class="btn" type="submit">Search</button>
          <a class="btn btn-outline" href="dashboard.php">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Books Table -->
  <div class="col-12">
    <div class="card">
      <h2 class="h2">Books</h2>

      <table class="table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Author</th>
            <th>ISBN</th>
            <th>Available</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($books)): ?>
          <tr><td colspan="5">No books found.</td></tr>
        <?php else: ?>
          <?php foreach ($books as $b): ?>
            <tr>
              <td><?php echo htmlspecialchars($b['title']); ?></td>
              <td><?php echo htmlspecialchars($b['author']); ?></td>
              <td><?php echo htmlspecialchars($b['isbn']); ?></td>
              <td>
                <?php if ((int)$b['available_copies'] > 0): ?>
                  <span class="badge ok"><?php echo (int)$b['available_copies']; ?> available</span>
                <?php else: ?>
                  <span class="badge err">0 available</span>
                <?php endif; ?>
              </td>
              <td>
                <form method="post" action="" style="display:flex; gap:8px;">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
                  <input type="hidden" name="book_id" value="<?php echo (int)$b['id']; ?>" />

                  <button class="btn btn-green" type="submit" name="action" value="borrow">Borrow</button>
                  <button class="btn btn-red" type="submit" name="action" value="return">Return</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>

      <p class="small" style="margin-top:10px;">
        Note: Borrow decreases available copies by 1 and creates a loan record.
        Return marks the last active loan as returned and increases available copies by 1.
      </p>
    </div>
  </div>

  <!-- Borrowing History -->
  <div class="col-12">
    <div class="card">
      <h2 class="h2">My Borrowing History</h2>
      <table class="table">
        <thead>
          <tr>
            <th>Book</th>
            <th>Borrowed</th>
            <th>Due</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($loans)): ?>
          <tr><td colspan="4">No loans yet.</td></tr>
        <?php else: ?>
          <?php foreach ($loans as $l): ?>
            <tr>
              <td>
                <?php echo htmlspecialchars($l['title']); ?>
                <span class="small">by <?php echo htmlspecialchars($l['author']); ?></span>
              </td>
              <td><?php echo htmlspecialchars($l['borrowed_at']); ?></td>
              <td><?php echo htmlspecialchars($l['due_at']); ?></td>
              <td>
                <?php if ($l['returned_at']): ?>
                  <span class="badge ok">Returned</span>
                <?php else: ?>
                  <span class="badge err">Borrowed</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

 
