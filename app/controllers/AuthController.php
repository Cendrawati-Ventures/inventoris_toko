<?php

class AuthController {
    private $user;

    public function __construct() {
        require_once BASE_PATH . '/app/models/User.php';
        $this->user = new User();
    }

    // Show login page
    public function login() {
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->user->login($username, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];
                header('Location: /');
                exit;
            } else {
                $error = 'Username atau password salah!';
            }
        }

        require_once BASE_PATH . '/app/views/auth/login.php';
    }

    // Logout
    public function logout() {
        session_destroy();
        header('Location: /login');
        exit;
    }

    // Show user management (admin only)
    public function users() {
        $this->checkAuth(['admin']);
        
        $users = $this->user->getAllUsers();
        require_once BASE_PATH . '/app/views/auth/users.php';
    }

    // Create user form
    public function createUser() {
        $this->checkAuth(['admin']);
        
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $nama = $_POST['nama'] ?? '';
            $role = $_POST['role'] ?? 'kasir';

            if (!$username || !$password || !$nama) {
                $error = 'Semua field harus diisi!';
            } elseif ($this->user->usernameExists($username)) {
                $error = 'Username sudah digunakan!';
            } else {
                if ($this->user->createUser($username, $password, $nama, $role)) {
                    header('Location: /users');
                    exit;
                } else {
                    $error = 'Gagal membuat user!';
                }
            }
        }

        require_once BASE_PATH . '/app/views/auth/create-user.php';
    }

    // Edit user form
    public function editUser($id) {
        $this->checkAuth(['admin']);
        
        $user = $this->user->getUserById($id);
        if (!$user) {
            header('Location: /users');
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $nama = $_POST['nama'] ?? '';
            $role = $_POST['role'] ?? 'kasir';

            if (!$username || !$nama) {
                $error = 'Semua field harus diisi!';
            } elseif ($this->user->usernameExists($username, $id)) {
                $error = 'Username sudah digunakan!';
            } else {
                if ($this->user->updateUser($id, $username, $nama, $role)) {
                    header('Location: /users');
                    exit;
                } else {
                    $error = 'Gagal mengupdate user!';
                }
            }
        }

        require_once BASE_PATH . '/app/views/auth/edit-user.php';
    }

    // Delete user
    public function deleteUser($id) {
        $this->checkAuth(['admin']);
        
        $this->user->deleteUser($id);
        header('Location: /users');
        exit;
    }

    // Check authentication and authorization
    private function checkAuth($allowedRoles = []) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        if (!empty($allowedRoles) && !in_array($_SESSION['role'], $allowedRoles)) {
            header('Location: /');
            exit;
        }
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole($roles) {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        if (!in_array($_SESSION['role'], (array)$roles)) {
            header('Location: /');
            exit;
        }
    }
}
