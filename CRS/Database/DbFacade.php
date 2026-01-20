<?php
require_once(realpath(dirname(__FILE__)) . '/../../config.php');
require_once(realpath(dirname(__FILE__)) . '/../../CRS/Database/DBConnection.php');
 
class DbFacade {
    private DBConnection $db;

    public function __construct() {
        $cfg = get_db_config();
        $this->db = new DBConnection($cfg['host'], $cfg['name'], $cfg['user'], $cfg['pass']);
    }

    private function pdo(): PDO {
        return $this->db->getConnection();
    }
 
    public function getUserByEmail(string $email): ?array {
        $stmt = $this->pdo()->prepare('SELECT id, name, email, password_hash, created_at FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createUser(string $name, string $email, string $passwordHash): int {
        $stmt = $this->pdo()->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :ph)');
        $stmt->execute([':name' => $name, ':email' => $email, ':ph' => $passwordHash]);
        return (int)$this->pdo()->lastInsertId();
    }
 
    public function listBooks(int $limit = 50): array {
        $stmt = $this->pdo()->prepare('SELECT id, title, author, isbn, total_copies, available_copies FROM books ORDER BY title ASC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

public function searchBooks(string $q, int $limit = 50): array
{
    $like = '%' . $q . '%';

    $stmt = $this->pdo()->prepare(
        'SELECT id, title, author, isbn, total_copies, available_copies
         FROM books
         WHERE title LIKE :q1
            OR author LIKE :q2
            OR isbn LIKE :q3
         ORDER BY title ASC
         LIMIT :lim'
    );

    $stmt->bindValue(':q1', $like, PDO::PARAM_STR);
    $stmt->bindValue(':q2', $like, PDO::PARAM_STR);
    $stmt->bindValue(':q3', $like, PDO::PARAM_STR);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    public function getBookById(int $bookId): ?array {
        $stmt = $this->pdo()->prepare('SELECT id, title, author, isbn, total_copies, available_copies FROM books WHERE id = :id');
        $stmt->execute([':id' => $bookId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
 
    public function getActiveLoan(int $userId, int $bookId): ?array {
        $stmt = $this->pdo()->prepare('SELECT id, user_id, book_id, borrowed_at, due_at, returned_at FROM loans WHERE user_id = :u AND book_id = :b AND returned_at IS NULL ORDER BY borrowed_at DESC LIMIT 1');
        $stmt->execute([':u' => $userId, ':b' => $bookId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function listUserLoans(int $userId): array {
        $stmt = $this->pdo()->prepare(
            'SELECT l.id, l.book_id, b.title, b.author, l.borrowed_at, l.due_at, l.returned_at
             FROM loans l
             JOIN books b ON b.id = l.book_id
             WHERE l.user_id = :u
             ORDER BY l.borrowed_at DESC'
        );
        $stmt->execute([':u' => $userId]);
        return $stmt->fetchAll();
    }

    public function borrowBook(int $userId, int $bookId, int $loanDays = 14): array {
        $pdo = $this->pdo();
        $pdo->beginTransaction();
        try {
          
            $stmt = $pdo->prepare('SELECT id, available_copies FROM books WHERE id = :id FOR UPDATE');
            $stmt->execute([':id' => $bookId]);
            $book = $stmt->fetch();
            if (!$book) {
                $pdo->rollBack();
                return ['ok' => false, 'message' => 'Book not found.'];
            }
            if ((int)$book['available_copies'] <= 0) {
                $pdo->rollBack();
                return ['ok' => false, 'message' => 'No copies available for borrowing.'];
            }
    
            $stmt = $pdo->prepare('SELECT id FROM loans WHERE user_id = :u AND book_id = :b AND returned_at IS NULL LIMIT 1 FOR UPDATE');
            $stmt->execute([':u' => $userId, ':b' => $bookId]);
            if ($stmt->fetch()) {
                $pdo->rollBack();
                return ['ok' => false, 'message' => 'You already borrowed this book and have not returned it yet.'];
            }

            $stmt = $pdo->prepare('UPDATE books SET available_copies = available_copies - 1 WHERE id = :id');
            $stmt->execute([':id' => $bookId]);

            $stmt = $pdo->prepare(
                'INSERT INTO loans (user_id, book_id, borrowed_at, due_at)
                 VALUES (:u, :b, NOW(), DATE_ADD(NOW(), INTERVAL :days DAY))'
            );
            $stmt->bindValue(':u', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':b', $bookId, PDO::PARAM_INT);
            $stmt->bindValue(':days', $loanDays, PDO::PARAM_INT);
            $stmt->execute();
            $pdo->commit();
            return ['ok' => true, 'message' => 'Borrowed successfully.'];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'message' => 'Borrow failed: ' . $e->getMessage()];
        }
    }

    public function returnBook(int $userId, int $bookId): array {
        $pdo = $this->pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT id FROM loans WHERE user_id = :u AND book_id = :b AND returned_at IS NULL ORDER BY borrowed_at DESC LIMIT 1 FOR UPDATE');
            $stmt->execute([':u' => $userId, ':b' => $bookId]);
            $loan = $stmt->fetch();
            if (!$loan) {
                $pdo->rollBack();
                return ['ok' => false, 'message' => 'No active loan found for this book.'];
            }

            $stmt = $pdo->prepare('UPDATE loans SET returned_at = NOW() WHERE id = :id');
            $stmt->execute([':id' => (int)$loan['id']]);

            $stmt = $pdo->prepare('UPDATE books SET available_copies = available_copies + 1 WHERE id = :id');
            $stmt->execute([':id' => $bookId]);

            $pdo->commit();
            return ['ok' => true, 'message' => 'Returned successfully.'];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'message' => 'Return failed: ' . $e->getMessage()];
        }
    }
}
