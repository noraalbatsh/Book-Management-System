<?php
require_once(realpath(dirname(__FILE__)) . '/../../CRS/Database/DbFacade.php');

/**
 * AuthManager: login verification.
 */
class AuthManager {
    private DbFacade $db;

    public function __construct(?DbFacade $dbFacade = null) {
        $this->db = $dbFacade ?: new DbFacade();
    }

    public function checkCredentials(string $email, string $password): array {
        $user = $this->db->getUserByEmail($email);
        if (!$user) {
            return ['ok' => false, 'message' => 'Invalid email or password.'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return ['ok' => false, 'message' => 'Invalid email or password.'];
        }

        return ['ok' => true, 'user' => ['id' => (int)$user['id'], 'name' => $user['name'], 'email' => $user['email']]];
    }
}
