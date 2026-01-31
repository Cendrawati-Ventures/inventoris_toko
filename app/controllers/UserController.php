<?php

class UserController {
    private $userModel;

    public function __construct() {
        require_once BASE_PATH . '/app/models/User.php';
        $this->userModel = new User();
    }

    // List semua user
    public function index() {
        // Hanya admin yang bisa akses
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini';
            header('Location: /');
            exit;
        }

        $users = $this->userModel->getAllUsers();
        
        // Urutkan admin di atas
        usort($users, function($a, $b) {
            if ($a['role'] === 'admin' && $b['role'] !== 'admin') {
                return -1;
            } elseif ($a['role'] !== 'admin' && $b['role'] === 'admin') {
                return 1;
            }
            return 0;
        });
        
        ob_start();
        ?>
        <div class="max-w-7xl mx-auto px-4">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-800 flex items-center gap-3">
                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-3">
                                <i class="fas fa-users text-white text-xl"></i>
                            </div>
                            Manajemen Pengguna
                        </h1>
                        <p class="text-gray-600 mt-2">Kelola daftar pengguna dan izin akses</p>
                    </div>
                    <a href="/user/create" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition transform hover:scale-105 shadow-lg">
                        <i class="fas fa-plus-circle"></i>Tambah Pengguna
                    </a>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-lg p-4 flex items-start gap-3 shadow-md">
                    <i class="fas fa-check-circle text-green-600 text-xl mt-1 flex-shrink-0"></i>
                    <div>
                        <p class="font-semibold text-green-800"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 rounded-lg p-4 flex items-start gap-3 shadow-md">
                    <i class="fas fa-exclamation-circle text-red-600 text-xl mt-1 flex-shrink-0"></i>
                    <div>
                        <p class="font-semibold text-red-800"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Table Container -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                                <th class="px-6 py-4 text-left font-semibold text-sm">No</th>
                                <th class="px-6 py-4 text-left font-semibold text-sm">Username</th>
                                <th class="px-6 py-4 text-left font-semibold text-sm">Nama Lengkap</th>
                                <th class="px-6 py-4 text-left font-semibold text-sm">Role</th>
                                <th class="px-6 py-4 text-center font-semibold text-sm">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <i class="fas fa-inbox text-gray-300 text-5xl"></i>
                                            <p class="text-gray-500 text-lg">Belum ada pengguna</p>
                                            <p class="text-gray-400 text-sm">Klik tombol "Tambah Pengguna" untuk menambahkan pengguna baru</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $index => $user): 
                                    $isAdmin = $user['role'] === 'admin';
                                    $isSelf = $user['id_user'] === $_SESSION['user_id'];
                                ?>
                                    <tr class="hover:bg-blue-50 transition duration-200 group">
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-600 rounded-full font-semibold text-sm">
                                                <?= $index + 1 ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br <?= $isAdmin ? 'from-red-400 to-red-600' : 'from-blue-400 to-blue-600' ?> flex items-center justify-center flex-shrink-0">
                                                    <i class="fas <?= $isAdmin ? 'fa-shield-alt' : 'fa-user' ?> text-white"></i>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($user['username']) ?></p>
                                                    <?php if ($isSelf): ?>
                                                        <span class="inline-block bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded font-semibold mt-1">
                                                            <i class="fas fa-user-check"></i> Akun Anda
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700"><?= htmlspecialchars($user['nama']) ?></td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-semibold <?= $isAdmin ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' ?>">
                                                <i class="fas <?= $isAdmin ? 'fa-crown' : 'fa-user-tie' ?>"></i>
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-center gap-2">
                                                <!-- Edit Button -->
                                                <a href="/user/edit/<?= $user['id_user'] ?>" class="inline-flex items-center justify-center w-9 h-9 bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg transition duration-200 group/btn" title="Edit Pengguna">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </a>
                                                
                                                <!-- Reset Password Button -->
                                                <a href="/user/reset-password/<?= $user['id_user'] ?>" class="inline-flex items-center justify-center w-9 h-9 bg-orange-100 text-orange-600 hover:bg-orange-600 hover:text-white rounded-lg transition duration-200 group/btn" title="Reset Password">
                                                    <i class="fas fa-key text-sm"></i>
                                                </a>
                                                
                                                <!-- Delete Button -->
                                                <?php if (!$isSelf): ?>
                                                    <a href="/user/delete/<?= $user['id_user'] ?>" class="inline-flex items-center justify-center w-9 h-9 bg-red-100 text-red-600 hover:bg-red-600 hover:text-white rounded-lg transition duration-200 group/btn" title="Hapus Pengguna" onclick="return confirm('Yakin ingin menghapus pengguna <?= htmlspecialchars($user['nama']) ?>?')">
                                                        <i class="fas fa-trash-alt text-sm"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center justify-center w-9 h-9 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed" title="Anda tidak bisa menghapus akun sendiri">
                                                        <i class="fas fa-lock text-sm"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-gray-600">
                <p>Total pengguna: <span class="font-semibold text-gray-800"><?= count($users) ?></span></p>
                <p class="text-gray-500">Admin: <span class="font-semibold text-gray-700"><?= count(array_filter($users, fn($u) => $u['role'] === 'admin')) ?></span> | Kasir: <span class="font-semibold text-gray-700"><?= count(array_filter($users, fn($u) => $u['role'] === 'kasir')) ?></span></p>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        $title = 'Manajemen Pengguna - Sistem Inventori';
        include __DIR__ . '/../views/layout/header.php';
    }

    // Form create user
    public function create() {
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini';
            header('Location: /');
            exit;
        }

        ob_start();
        ?>
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-user-plus text-blue-600 mr-2"></i>Tambah Pengguna Baru
                </h2>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="/user/store" method="POST">
                    <div class="mb-4">
                        <label for="username" class="block text-gray-700 font-semibold mb-2">
                            Username <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="username" name="username" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               required>
                        <small class="text-gray-500">Gunakan untuk login</small>
                    </div>

                    <div class="mb-4">
                        <label for="nama" class="block text-gray-700 font-semibold mb-2">
                            Nama Lengkap <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="nama" name="nama" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 font-semibold mb-2">
                            Password <span class="text-red-600">*</span>
                        </label>
                        <input type="password" id="password" name="password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               required>
                        <small class="text-gray-500">Minimal 6 karakter</small>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="block text-gray-700 font-semibold mb-2">
                            Konfirmasi Password <span class="text-red-600">*</span>
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               required>
                    </div>

                    <div class="mb-6">
                        <label for="role" class="block text-gray-700 font-semibold mb-2">
                            Role <span class="text-red-600">*</span>
                        </label>
                        <select id="role" name="role" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                required>
                            <option value="">-- Pilih Role --</option>
                            <option value="kasir">Kasir</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition font-semibold flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i>Simpan Pengguna
                        </button>
                        <a href="/user" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition font-semibold flex items-center justify-center gap-2">
                            <i class="fas fa-arrow-left"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        $title = 'Tambah Pengguna - Sistem Inventori';
        include __DIR__ . '/../views/layout/header.php';
    }

    // Store user baru
    public function store() {
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses';
            header('Location: /user');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /user/create');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? '';

        // Validasi
        if (empty($username) || empty($nama) || empty($password) || empty($role)) {
            $_SESSION['error'] = 'Semua field harus diisi';
            header('Location: /user/create');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password minimal 6 karakter';
            header('Location: /user/create');
            exit;
        }

        if ($password !== $confirm_password) {
            $_SESSION['error'] = 'Password tidak cocok';
            header('Location: /user/create');
            exit;
        }

        if ($this->userModel->usernameExists($username)) {
            $_SESSION['error'] = 'Username sudah digunakan';
            header('Location: /user/create');
            exit;
        }

        if (!in_array($role, ['admin', 'kasir'])) {
            $_SESSION['error'] = 'Role tidak valid';
            header('Location: /user/create');
            exit;
        }

        if ($this->userModel->createUser($username, $password, $nama, $role)) {
            $_SESSION['success'] = 'Pengguna berhasil ditambahkan';
            header('Location: /user');
            exit;
        } else {
            $_SESSION['error'] = 'Gagal menambahkan pengguna';
            header('Location: /user/create');
            exit;
        }
    }

    // Form edit user
    public function edit($id) {
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses';
            header('Location: /');
            exit;
        }

        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $_SESSION['error'] = 'Pengguna tidak ditemukan';
            header('Location: /user');
            exit;
        }

        ob_start();
        ?>
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-user-edit text-blue-600 mr-2"></i>Edit Pengguna
                </h2>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="/user/update/<?= $user['id_user'] ?>" method="POST">
                    <div class="mb-4">
                        <label for="username" class="block text-gray-700 font-semibold mb-2">
                            Username <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="username" name="username" 
                               value="<?= htmlspecialchars($user['username']) ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label for="nama" class="block text-gray-700 font-semibold mb-2">
                            Nama Lengkap <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="nama" name="nama" 
                               value="<?= htmlspecialchars($user['nama']) ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               required>
                    </div>

                    <div class="mb-6">
                        <label for="role" class="block text-gray-700 font-semibold mb-2">
                            Role <span class="text-red-600">*</span>
                        </label>
                        <select id="role" name="role" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                required>
                            <option value="kasir" <?= $user['role'] === 'kasir' ? 'selected' : '' ?>>Kasir</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition font-semibold flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i>Update Pengguna
                        </button>
                        <a href="/user" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition font-semibold flex items-center justify-center gap-2">
                            <i class="fas fa-arrow-left"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        $title = 'Edit Pengguna - Sistem Inventori';
        include __DIR__ . '/../views/layout/header.php';
    }

    // Update user
    public function update($id) {
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses';
            header('Location: /user');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /user/edit/' . $id);
            exit;
        }

        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $_SESSION['error'] = 'Pengguna tidak ditemukan';
            header('Location: /user');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $role = $_POST['role'] ?? '';

        if (empty($username) || empty($nama) || empty($role)) {
            $_SESSION['error'] = 'Semua field harus diisi';
            header('Location: /user/edit/' . $id);
            exit;
        }

        if ($this->userModel->usernameExists($username, $id)) {
            $_SESSION['error'] = 'Username sudah digunakan oleh pengguna lain';
            header('Location: /user/edit/' . $id);
            exit;
        }

        if ($this->userModel->updateUser($id, $username, $nama, $role)) {
            $_SESSION['success'] = 'Pengguna berhasil diupdate';
            header('Location: /user');
            exit;
        } else {
            $_SESSION['error'] = 'Gagal update pengguna';
            header('Location: /user/edit/' . $id);
            exit;
        }
    }

    // Form reset password
    public function resetPasswordForm($id) {
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses';
            header('Location: /');
            exit;
        }

        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $_SESSION['error'] = 'Pengguna tidak ditemukan';
            header('Location: /user');
            exit;
        }

        ob_start();
        ?>
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-key text-blue-600 mr-2"></i>Reset Password
                </h2>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-gray-700"><strong>Pengguna:</strong> <?= htmlspecialchars($user['nama']) ?> (<?= htmlspecialchars($user['username']) ?>)</p>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="/user/update-password/<?= $user['id_user'] ?>" method="POST">
                    <div class="mb-4">
                        <label for="new_password" class="block text-gray-700 font-semibold mb-2">
                            Password Baru <span class="text-red-600">*</span>
                        </label>
                        <input type="password" id="new_password" name="new_password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               required>
                        <small class="text-gray-500">Minimal 6 karakter</small>
                    </div>

                    <div class="mb-6">
                        <label for="confirm_password" class="block text-gray-700 font-semibold mb-2">
                            Konfirmasi Password <span class="text-red-600">*</span>
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               required>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg transition font-semibold flex items-center justify-center gap-2">
                            <i class="fas fa-key"></i>Reset Password
                        </button>
                        <a href="/user" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition font-semibold flex items-center justify-center gap-2">
                            <i class="fas fa-arrow-left"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        $title = 'Reset Password - Sistem Inventori';
        include __DIR__ . '/../views/layout/header.php';
    }

    // Update password
    public function updatePassword($id) {
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses';
            header('Location: /user');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /user/reset-password/' . $id);
            exit;
        }

        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $_SESSION['error'] = 'Pengguna tidak ditemukan';
            header('Location: /user');
            exit;
        }

        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($new_password)) {
            $_SESSION['error'] = 'Password tidak boleh kosong';
            header('Location: /user/reset-password/' . $id);
            exit;
        }

        if (strlen($new_password) < 6) {
            $_SESSION['error'] = 'Password minimal 6 karakter';
            header('Location: /user/reset-password/' . $id);
            exit;
        }

        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = 'Password tidak cocok';
            header('Location: /user/reset-password/' . $id);
            exit;
        }

        if ($this->userModel->resetPassword($id, $new_password)) {
            $_SESSION['success'] = 'Password pengguna berhasil direset';
            header('Location: /user');
            exit;
        } else {
            $_SESSION['error'] = 'Gagal reset password';
            header('Location: /user/reset-password/' . $id);
            exit;
        }
    }

    // Delete user
    public function delete($id) {
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses';
            header('Location: /user');
            exit;
        }

        if ($id === $_SESSION['user_id']) {
            $_SESSION['error'] = 'Anda tidak bisa menghapus akun sendiri';
            header('Location: /user');
            exit;
        }

        if ($this->userModel->deleteUser($id)) {
            $_SESSION['success'] = 'Pengguna berhasil dihapus';
            header('Location: /user');
            exit;
        } else {
            $_SESSION['error'] = 'Gagal menghapus pengguna';
            header('Location: /user');
            exit;
        }
    }
}
