<?php
require_once(realpath(dirname(__FILE__)) . '/../../CRS/Database/DbFacade.php');
require_once(realpath(dirname(__FILE__)) . '/../../CRS/AppLogic/AuthManager.php');
require_once(realpath(dirname(__FILE__)) . '/../../CRS/AppLogic/RegisterController_.php');

/**
 * Application Logic Facade.
 */
class AlFacade {
    private DbFacade $db;
    private AuthManager $auth;
    private RegisterController_ $register;

    public function __construct() {
        $this->db = new DbFacade();
        $this->auth = new AuthManager($this->db);
        $this->register = new RegisterController_($this->db);
    }

    public function login(string $email, string $password): array {
        return $this->auth->checkCredentials($email, $password);
    }

    public function registerMember(string $name, string $email, string $password): array {
        return $this->register->registerMember($name, $email, $password);
    }

    public function searchBooks(string $q): array {
        return $this->db->searchBooks($q);
    }

    public function listBooks(): array {
        return $this->db->listBooks();
    }

    public function listUserLoans(int $userId): array {
        return $this->db->listUserLoans($userId);
    }

    public function borrowBook(int $userId, int $bookId): array {
        return $this->db->borrowBook($userId, $bookId);
    }

    public function returnBook(int $userId, int $bookId): array {
        return $this->db->returnBook($userId, $bookId);
    }
}
