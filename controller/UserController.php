<?php
/**
 * User Controller Class
 * Handles all user-related operations
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/UserModel.php';

class UserController extends BaseController {
    private $userModel;

    public function __construct() {
        parent::__construct();
        if ($this->isConnected()) {
            $this->userModel = new UserModel($this->db);
        }
    }

    public function createUser($userData) {
        if (!$this->isConnected()) return false;
        
        // Extract data from array
        $username = $userData['username'];
        $password = $userData['password'];
        $email = $userData['email'];
        $full_name = $userData['full_name'] ?? null;
        $phone = $userData['phone'] ?? null;
        $address = $userData['address'] ?? null;
        $role = $userData['role'] ?? 'user';
        
        // Check if username or email already exists
        if ($this->userModel->usernameExists($username)) {
            throw new Exception("Username already exists");
        }
        
        if ($this->userModel->emailExists($email)) {
            throw new Exception("Email already exists");
        }
        
        return $this->userModel->create($username, $password, $email, $full_name, $phone, $address, $role);
    }

    public function loginUser($username, $password) {
        if (!$this->isConnected()) return false;
        return $this->userModel->verifyLogin($username, $password);
    }

    public function verifyUserLogin($username, $password) {
        if (!$this->isConnected()) return false;
        return $this->userModel->verifyLogin($username, $password);
    }

    public function usernameExists($username) {
        if (!$this->isConnected()) return false;
        return $this->userModel->usernameExists($username);
    }

    public function isUsernameExists($username) {
        if (!$this->isConnected()) return false;
        return $this->userModel->usernameExists($username);
    }

    public function emailExists($email) {
        if (!$this->isConnected()) return false;
        return $this->userModel->emailExists($email);
    }

    public function isEmailExists($email) {
        if (!$this->isConnected()) return false;
        return $this->userModel->emailExists($email);
    }

    public function getUserById($user_id) {
        if (!$this->isConnected()) return false;
        return $this->userModel->getById($user_id);
    }

    public function getAllUsers($limit = null, $offset = null) {
        if (!$this->isConnected()) return false;
        return $this->userModel->getAll($limit, $offset);
    }

    public function updateUser($user_id, $data) {
        if (!$this->isConnected()) return false;
        return $this->userModel->update($user_id, $data);
    }

    public function deleteUser($user_id) {
        if (!$this->isConnected()) return false;
        return $this->userModel->delete($user_id);
    }

    public function isUserActive($user_id) {
        if (!$this->isConnected()) return false;
        return $this->userModel->isActive($user_id);
    }

    public function getUsersByStatus($status, $limit = null, $offset = null) {
        if (!$this->isConnected()) return false;
        return $this->userModel->getByStatus($status, $limit, $offset);
    }

    public function getUsersCount() {
        if (!$this->isConnected()) return false;
        return $this->userModel->getCount();
    }
}
?>
