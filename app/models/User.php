<?php

class User {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Login user
    public function login($username, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    // Get user by ID
    public function getUserById($id) {
        try {
            $stmt = $this->db->prepare("SELECT id_user, username, nama, role FROM users WHERE id_user = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    // Get all users
    public function getAllUsers() {
        try {
            $stmt = $this->db->prepare("SELECT id_user, username, nama, role FROM users ORDER BY id_user DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Create user
    public function createUser($username, $password, $nama, $role = 'kasir') {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$username, $hashedPassword, $nama, $role]);
        } catch (Exception $e) {
            return false;
        }
    }

    // Update user
    public function updateUser($id, $username, $nama, $role) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET username = ?, nama = ?, role = ? WHERE id_user = ?");
            return $stmt->execute([$username, $nama, $role, $id]);
        } catch (Exception $e) {
            return false;
        }
    }

    // Delete user
    public function deleteUser($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id_user = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            return false;
        }
    }

    // Check username exists
    public function usernameExists($username, $excludeId = null) {
        try {
            if ($excludeId) {
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE username = ? AND id_user != ?");
                $stmt->execute([$username, $excludeId]);
            } else {
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
                $stmt->execute([$username]);
            }
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    // Reset password user
    public function resetPassword($id, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id_user = ?");
            return $stmt->execute([$hashedPassword, $id]);
        } catch (Exception $e) {
            return false;
        }
    }

    // Change password user
    public function changePassword($id, $oldPassword, $newPassword) {
        try {
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id_user = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($oldPassword, $user['password'])) {
                return false;
            }

            return $this->resetPassword($id, $newPassword);
        } catch (Exception $e) {
            return false;
        }
    }

    // Get all kasir only
    public function getAllKasir() {
        try {
            $stmt = $this->db->prepare("SELECT id_user, username, nama FROM users WHERE role = 'kasir' ORDER BY id_user DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}
