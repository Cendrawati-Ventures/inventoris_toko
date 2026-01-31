# 📋 Quick Reference - User Management

## 🎯 Main Tasks

### Task 1: Tambah Kasir Baru ⭐⭐⭐ (Sering)
**Tujuan**: Menambahkan user kasir baru ke sistem
**Waktu**: ~2 menit

**Steps**:
1. Login sebagai Admin
2. Click **Pengaturan** → **Manajemen User**
3. Click **Tambah User** (tombol biru)
4. Isi form:
   - Username: `kasir01` (atau nama kasir)
   - Nama: `Budi Santoso`
   - Password: `kasir123` (minimal 6 karakter)
   - Konfirmasi: ulangi password
   - Role: **Kasir**
5. Click **Simpan User**
6. ✅ Kasir bisa login

**Error Handling**:
- Username sudah ada? Ganti dengan nama lain
- Password kurang 6 karakter? Gunakan password lebih panjang
- Form error? Refresh dan coba lagi

---

### Task 2: Reset Password Kasir ⭐⭐ (Kadang)
**Tujuan**: Reset password kasir yang lupa
**Waktu**: ~1 menit

**Steps**:
1. Login sebagai Admin
2. Click **Pengaturan** → **Manajemen User**
3. Cari kasir di tabel
4. Click icon **🔑 Kunci** di kolom Aksi
5. Isi password baru:
   - Password Baru: `newpass123`
   - Konfirmasi: ulangi password
6. Click **Reset Password**
7. ✅ Kasir bisa login dengan password baru

---

### Task 3: Edit User Info ⭐ (Jarang)
**Tujuan**: Mengubah username, nama, atau role user
**Waktu**: ~1 menit

**Steps**:
1. Login sebagai Admin
2. Click **Pengaturan** → **Manajemen User**
3. Cari user di tabel
4. Click icon **✏️ Edit** di kolom Aksi
5. Ubah yang diperlukan:
   - Username: baru username
   - Nama: nama baru
   - Role: ubah ke Admin atau Kasir
6. Click **Update User**
7. ✅ User info berhasil diubah

---

### Task 4: Hapus User ⭐ (Jarang)
**Tujuan**: Menghapus user yang sudah resign
**Waktu**: ~1 menit

**Steps**:
1. Login sebagai Admin
2. Click **Pengaturan** → **Manajemen User**
3. Cari user di tabel
4. Click icon **🗑️ Trash** di kolom Aksi
5. Confirm dialog: "Yakin ingin menghapus user ini?"
6. Click **OK** atau **Yes**
7. ✅ User dihapus

**Note**: 
- Tidak bisa hapus akun sendiri
- Data transaksi user tetap tersimpan

---

## 📋 Daftar User

Tabel menampilkan:
| Kolom | Arti |
|-------|------|
| ID | Nomor unik user |
| Username | Username login |
| Nama | Nama lengkap |
| Role | Admin (merah) atau Kasir (biru) |
| Aksi | Tombol untuk Edit, Reset Password, Hapus |

---

## 🔑 Password Tips

### Membuat Password Baik
✅ **Baik**: `Kasir2024!`, `BudiKasir123`, `toko#santoso`
❌ **Buruk**: `123456`, `qwerty`, `password`, `12345678`

### Password Requirements
- Minimal 6 karakter
- Lebih panjang = Lebih aman
- Pakai kombinasi huruf + angka lebih aman

---

## 🚨 Jangan Lakukan

| Aksi | Alasan |
|------|--------|
| ❌ Share password | Kasir akan sharing login ke orang lain |
| ❌ Username sama | Setiap user harus username unik |
| ❌ Password terlalu pendek | Mudah ditebak |
| ❌ Hapus akun sendiri | Tidak bisa, sistem proteksi |
| ❌ Beri akses admin ke kasir | Kasir akan ubah setting |

---

## ✅ Checklist: Setup Kasir Baru

Sebelum kasir bisa kerja:

- [ ] Buat user di sistem (role: Kasir)
- [ ] Kasir bisa login dengan username & password
- [ ] Kasir bisa akses menu penjualan & pembelian
- [ ] Kasir tidak bisa akses menu Pengaturan/Admin
- [ ] Test dengan device/komputer kasir
- [ ] Dokumentasikan username & password (aman)

---

## 🆘 Troubleshooting

### Kasir tidak bisa login
**Solusi**:
1. Cek username benar (case-sensitive)
2. Cek password benar
3. Pastikan role = "Kasir"
4. Reset password jika perlu

### Username sudah ada
**Solusi**: Gunakan username berbeda (misal `kasir02`, `kasir_budi`, dll)

### Lupa mana kasir yang siapa
**Solusi**: Lihat kolom "Nama" dan "Username" di tabel user

### Tidak bisa delete user
**Solusi**: Jika user adalah diri sendiri, tidak bisa delete. Minta help dari admin lain.

---

## 📱 Mobile Access

User management bisa diakses dari:
- Desktop/Laptop (recommended)
- Tablet
- Mobile (tabel agak kecil, tapi tetap bisa digunakan)

---

## 📊 Current Users

Untuk melihat kasir apa saja yang aktif:
1. Click **Pengaturan** → **Manajemen User**
2. Lihat tabel, filter berdasarkan role "Kasir"

---

## 🔐 Security Reminder

1. **Password Private**: Jangan share password
2. **Regular Reset**: Reset password kasir yang lama resign
3. **Strong Password**: Gunakan password yang kuat (6+ char)
4. **Log Out**: Kasir selalu logout sebelum pulang
5. **Admin Access**: Jangan beri akses admin ke kasir

---

## 📞 Emergency

**Jika terjadi masalah besar**:
1. Contact: Developer/IT Support
2. Describe: Apa yang terjadi & kapan
3. Urgency: Apa akibatnya (critical/medium/low)
4. Status: Bisa pakai sistem atau tidak

---

## 🎓 Training Kasir Baru

Beri tahu kasir:

1. **Login**: Gunakan username & password dari admin
2. **Menu**: Kasir hanya bisa akses Barang, Pembelian, Penjualan, Hutang, Laporan Penjualan
3. **Pengaturan**: Kasir tidak bisa akses menu Pengaturan (admin only)
4. **Logout**: Selalu logout setelah selesai kerja
5. **Lupa Password**: Hubungi admin untuk reset

---

**Last Updated**: January 31, 2026
**Quick Reference v1.0**
