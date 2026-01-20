<?php
require_once(realpath(dirname(__FILE__)) . '/../../CRS/Database/DbFacade.php');

/**
 * Handles user registration.
 */
class RegisterController_ {
    private DbFacade $db;

    public function __construct(?DbFacade $dbFacade = null) {
        $this->db = $dbFacade ?: new DbFacade();
    }

    public function registerMember(string $name, string $email, string $password): array {
        $name = trim($name);
        $email = trim(strtolower($email));

        $validation = $this->validateData($name, $email, $password);
        if (!$validation['ok']) {
            return $validation;
        }

        if ($this->db->getUserByEmail($email)) {
            return ['ok' => false, 'message' => 'Email is already registered.'];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->db->createUser($name, $email, $hash);

        return ['ok' => true, 'message' => 'Registration successful. You can now log in.'];
    }

    public function validateData(string $name, string $email, string $password): array {
        if ($name === '' || strlen($name) < 2) {
            return ['ok' => false, 'message' => 'Name must be at least 2 characters.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'message' => 'Please enter a valid email.'];
        }
        if (strlen($password) < 6) {
            return ['ok' => false, 'message' => 'Password must be at least 6 characters.'];
        }
        return ['ok' => true];
    }
}
