# 👥 Panduan User Management - Sistem Inventori

## Fitur yang Ditambahkan

### 1. **Manajemen User (Admin Only)**
   - Lihat daftar semua user
   - Tambah user baru (admin atau kasir)
   - Edit informasi user
   - Reset password user
   - Hapus user

### 2. **Reset Password**
   - Admin dapat mereset password user tanpa mengetahui password lama
   - Validasi password minimum 6 karakter
   - Konfirmasi password untuk keamanan

---

## 📋 Akses Fitur

### Menu Navigation
Masuk ke sistem → **Pengaturan** → **Manajemen User**

### URL Langsung
- Lihat User: `/user`
- Tambah User: `/user/create`
- Edit User: `/user/edit/{id}`
- Reset Password: `/user/reset-password/{id}`

---

## 🚀 Step-by-Step: Menambah Kasir

### 1. Masuk sebagai Admin
- Login dengan akun admin

### 2. Buka Manajemen User
- Click menu **Pengaturan** → **Manajemen User**
- Atau akses langsung ke `/user`

### 3. Click "Tambah User"
- Isi form:
  - **Username**: Username unik untuk login (misal: `kasir01`)
  - **Nama Lengkap**: Nama kasir (misal: `Budi Santoso`)
  - **Password**: Password minimal 6 karakter (misal: `kasir123`)
  - **Konfirmasi Password**: Ulangi password yang sama
  - **Role**: Pilih "Kasir"

### 4. Click "Simpan User"
- User kasir berhasil ditambahkan
- Kasir bisa login dengan username dan password yang telah dibuat

---

## 🔑 Step-by-Step: Reset Password User

### 1. Buka Manajemen User
- Login sebagai admin
- Buka **Pengaturan** → **Manajemen User**

### 2. Cari User yang Password-nya Ingin Direset
- Scroll daftar user atau cari di tabel
- Klik icon **Kunci** (🔑) di kolom Aksi

### 3. Masukkan Password Baru
- **Password Baru**: Input password baru (minimal 6 karakter)
- **Konfirmasi Password**: Ulangi password
- Click "Reset Password"

### 4. Konfirmasi Berhasil
- Password user berhasil direset
- User bisa login dengan password baru

---

## 👤 Status User

### Admin
- Full access ke semua fitur
- Bisa manage user, setting, laporan lengkap

### Kasir
- Akses fitur transaksi (pembelian, penjualan, hutang)
- Akses laporan penjualan saja
- Tidak bisa akses pengaturan

---

## 🔒 Keamanan

### Best Practices
1. **Jangan berbagi password** dengan kasir di chat atau email
2. **Reset password regular** jika ada pergantian kasir
3. **Hapus user** yang sudah tidak bekerja
4. **Password minimal 6 karakter**, lebih panjang lebih aman

### Password Default
Tidak ada password default. Setiap user harus membuat password unik saat ditambahkan.

---

## 📊 Daftar User

### Kolom di Tabel
- **ID**: Nomor unik user
- **Username**: Username untuk login
- **Nama**: Nama lengkap user
- **Role**: Admin atau Kasir
- **Aksi**: Edit, Reset Password, Hapus

### Aksi yang Bisa Dilakukan
- **Edit** (Pencil Icon): Ubah username, nama, atau role
- **Reset Password** (Key Icon): Reset password tanpa tahu password lama
- **Hapus** (Trash Icon): Menghapus user (tidak bisa hapus akun sendiri)

---

## ⚠️ Perhatian

### Tidak Bisa Dilakukan
- ❌ Hapus akun admin sendiri
- ❌ Login dengan kasir di pengaturan admin
- ❌ Password kurang dari 6 karakter
- ❌ Username yang sudah digunakan user lain

### Jika Lupa Password Admin
Hubungi developer/IT untuk reset database atau akses langsung.

---

## 💡 Tips

### Membuat Username Kasir
- Gunakan format sederhana: `kasir01`, `kasir02`, dll
- Atau pakai nama: `budi_santoso`, `ani_wijaya`, dll
- Hindari karakter spesial

### Membuat Password Kuat
- Kombinasi huruf + angka: `kasir123`, `toko2024`
- Minimal 8 karakter lebih aman: `KasirToko2024`

### Dokumentasi User
Catat di buku atau spreadsheet:
| No | Username | Nama | Role | Tanggal |
|----|----------|------|------|---------|
| 1  | admin    | Admin Utama | Admin | 2024-01-01 |
| 2  | kasir01  | Budi | Kasir | 2024-01-15 |

---

## 🆘 Troubleshooting

### User tidak bisa login
- Cek username benar
- Cek password benar
- Cek role user (jika kasir, tidak bisa akses admin)

### Username sudah ada
- Gunakan username yang berbeda
- Username harus unik di sistem

### Lupa reset mana user
- Lihat di tabel Manajemen User
- Filter berdasarkan role atau nama

### Role salah saat create
- Edit user dan ubah rolenya
- Click icon Edit (Pencil)

---

## 🔄 Workflow Standar

### Saat Kasir Baru Masuk
1. ✅ Admin login ke sistem
2. ✅ Buka **Pengaturan** → **Manajemen User**
3. ✅ Click **Tambah User**
4. ✅ Isi data kasir baru
5. ✅ Pilih role: Kasir
6. ✅ Click **Simpan User**
7. ✅ Kasir bisa login dengan username & password yang dibuat

### Saat Kasir Lupa Password
1. ✅ Admin login ke sistem
2. ✅ Buka **Pengaturan** → **Manajemen User**
3. ✅ Cari kasir yang lupa password
4. ✅ Click icon **Kunci** (Reset Password)
5. ✅ Masukkan password baru
6. ✅ Click **Reset Password**
7. ✅ Kasir login dengan password baru

### Saat Kasir Resign
1. ✅ Admin login ke sistem
2. ✅ Buka **Pengaturan** → **Manajemen User**
3. ✅ Cari kasir yang resign
4. ✅ Click icon **Trash** (Hapus)
5. ✅ Konfirmasi penghapusan
6. ✅ User dihapus dari sistem

---

## 📞 Support

Jika ada kendala atau pertanyaan, hubungi admin sistem atau developer.

**Created**: January 31, 2026
**Version**: 1.0
