# 🔧 API Reference - User Management

## Endpoints

### 1. List All Users
**GET** `/user`

**Akses**: Admin only

**Response**: Halaman HTML dengan daftar user

**Contoh URL**:
```
GET /user
```

---

### 2. Form Create User
**GET** `/user/create`

**Akses**: Admin only

**Response**: Halaman form tambah user

---

### 3. Store User Baru
**POST** `/user/store`

**Akses**: Admin only

**Parameters**:
```
POST /user/store
Content-Type: application/x-www-form-urlencoded

username=kasir01&nama=Budi+Santoso&password=kasir123&confirm_password=kasir123&role=kasir
```

**Form Fields**:
| Field | Type | Required | Validasi |
|-------|------|----------|----------|
| username | string | Ya | Unik, tidak boleh duplikat |
| nama | string | Ya | Minimal 3 karakter |
| password | string | Ya | Minimal 6 karakter |
| confirm_password | string | Ya | Harus sama dengan password |
| role | enum | Ya | 'admin' atau 'kasir' |

**Success Response**: Redirect ke `/user` dengan session success

**Error Response**: Redirect ke `/user/create` dengan session error

**Possible Errors**:
- All fields required
- Password minimal 6 karakter
- Password tidak cocok
- Username sudah digunakan
- Role tidak valid

---

### 4. Form Edit User
**GET** `/user/edit/{id}`

**Akses**: Admin only

**Parameters**:
| Parameter | Type | Required |
|-----------|------|----------|
| id | integer | Ya |

**Response**: Halaman form edit user

**Contoh**:
```
GET /user/edit/2
```

---

### 5. Update User
**POST** `/user/update/{id}`

**Akses**: Admin only

**Parameters**:
| Parameter | Type | Required | Validasi |
|-----------|------|----------|----------|
| id | integer | Ya | User harus exist |
| username | string | Ya | Unik (exclude current user) |
| nama | string | Ya | Tidak kosong |
| role | enum | Ya | 'admin' atau 'kasir' |

**Form Data**:
```
POST /user/update/2
Content-Type: application/x-www-form-urlencoded

username=budi_santoso&nama=Budi+Santoso&role=kasir
```

**Success Response**: Redirect ke `/user` dengan session success

**Error Response**: Redirect ke `/user/edit/{id}` dengan session error

---

### 6. Form Reset Password
**GET** `/user/reset-password/{id}`

**Akses**: Admin only

**Parameters**:
| Parameter | Type | Required |
|-----------|------|----------|
| id | integer | Ya |

**Response**: Halaman form reset password

**Contoh**:
```
GET /user/reset-password/3
```

---

### 7. Update Password
**POST** `/user/update-password/{id}`

**Akses**: Admin only

**Parameters**:
| Parameter | Type | Required | Validasi |
|-----------|------|----------|----------|
| id | integer | Ya | User harus exist |
| new_password | string | Ya | Minimal 6 karakter |
| confirm_password | string | Ya | Harus sama dengan new_password |

**Form Data**:
```
POST /user/update-password/3
Content-Type: application/x-www-form-urlencoded

new_password=password123&confirm_password=password123
```

**Success Response**: Redirect ke `/user` dengan session success

**Error Response**: Redirect ke `/user/reset-password/{id}` dengan session error

**Possible Errors**:
- Password tidak boleh kosong
- Password minimal 6 karakter
- Password tidak cocok

---

### 8. Delete User
**GET** `/user/delete/{id}`

**Akses**: Admin only

**Parameters**:
| Parameter | Type | Required |
|-----------|------|----------|
| id | integer | Ya |

**Validasi**:
- User harus exist
- Tidak bisa menghapus akun sendiri

**Response**: Redirect ke `/user` dengan session success/error

**Contoh**:
```
GET /user/delete/4
```

---

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Request berhasil |
| 302 | Redirect - Redirect ke halaman lain |
| 404 | Not Found - User atau halaman tidak ditemukan |

---

## Session Variables

### Setelah Success
```php
$_SESSION['success'] = 'User berhasil ditambahkan';
// atau
$_SESSION['success'] = 'User berhasil diupdate';
// atau
$_SESSION['success'] = 'Password user berhasil direset';
// atau
$_SESSION['success'] = 'User berhasil dihapus';
```

### Setelah Error
```php
$_SESSION['error'] = 'Semua field harus diisi';
// atau error message lainnya
```

---

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'kasir') NOT NULL DEFAULT 'kasir',
    nama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## Model Methods

### User Model

```php
// Get all users
$user->getAllUsers()
// Return: Array of users

// Get user by ID
$user->getUserById($id)
// Return: User object or false

// Get all kasir only
$user->getAllKasir()
// Return: Array of kasir users

// Create user
$user->createUser($username, $password, $nama, $role = 'kasir')
// Return: boolean

// Update user
$user->updateUser($id, $username, $nama, $role)
// Return: boolean

// Reset password
$user->resetPassword($id, $newPassword)
// Return: boolean

// Change password
$user->changePassword($id, $oldPassword, $newPassword)
// Return: boolean

// Check username exists
$user->usernameExists($username, $excludeId = null)
// Return: boolean

// Delete user
$user->deleteUser($id)
// Return: boolean
```

---

## Controller Flow

### Create User Flow
1. User click "Tambah User"
2. GET `/user/create` → UserController::create()
3. Display form
4. User submit form
5. POST `/user/store` → UserController::store()
6. Validate input
7. Check username exists
8. Hash password
9. Insert ke database
10. Redirect dengan success/error message

### Reset Password Flow
1. Admin click icon Reset Password
2. GET `/user/reset-password/{id}` → UserController::resetPasswordForm()
3. Display reset form
4. Admin input password baru
5. POST `/user/update-password/{id}` → UserController::updatePassword()
6. Validate password
7. Hash password
8. Update di database
9. Redirect dengan success message

---

## Error Handling

### Input Validation
- Semua field harus diisi
- Password minimal 6 karakter
- Username harus unik
- Role harus valid ('admin' atau 'kasir')
- Password harus cocok dengan confirm password

### Business Logic Validation
- Tidak bisa menghapus user sendiri
- User harus exist untuk di-edit atau di-delete
- Hanya admin yang bisa akses fitur ini

---

## Security Measures

1. **Password Hashing**: Menggunakan `password_hash()` dengan PASSWORD_DEFAULT
2. **Admin Only**: Semua endpoint hanya bisa diakses admin
3. **CSRF Protection**: POST request harus valid
4. **Input Validation**: Semua input divalidasi
5. **SQL Injection Protection**: Menggunakan prepared statements (PDO)

---

## Testing

### Test Case: Tambah User
```
1. POST /user/store
   - username: kasir01
   - nama: Budi
   - password: kasir123
   - confirm_password: kasir123
   - role: kasir
   
   Expected: Redirect ke /user dengan success message
```

### Test Case: Reset Password
```
1. GET /user/reset-password/2
   Expected: Form reset password muncul
   
2. POST /user/update-password/2
   - new_password: newpass123
   - confirm_password: newpass123
   
   Expected: Redirect ke /user dengan success message
```

### Test Case: Delete User
```
1. GET /user/delete/4
   Expected: User dihapus, redirect ke /user dengan success message
```

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-01-31 | Initial release - User management, reset password |

---

**Last Updated**: January 31, 2026
