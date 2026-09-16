# Sistem Inventori Toko

Sistem manajemen inventori berbasis web menggunakan PHP dan Tailwind CSS.

## Fitur Utama

- **Manajemen Barang**: Tambah, edit, hapus, dan lihat daftar barang
- **Pembelian**: Catat transaksi pembelian barang dengan update stok otomatis
- **Penjualan**: Catat transaksi penjualan dengan validasi stok
- **Laporan**:
  - Laporan Pembelian
  - Laporan Penjualan
  - Laporan Stok Barang
  - Laporan Keuntungan
- **Dashboard**: Statistik dan informasi ringkas

## Teknologi

- PHP 7.4+
- MySQL
- Tailwind CSS (via CDN)
- Font Awesome Icons

## Instalasi

### 1. Persiapan Database

```sql
-- Import file database/skema.sql ke MySQL
mysql -u root -p < database/skema.sql
```

### 2. Konfigurasi Database

Edit file `app/config/database.php` sesuai dengan konfigurasi database Anda:

```php
private $host = 'localhost';
private $db_name = 'toko_inventori';
private $username = 'root';
private $password = '';
```

### 3. Setup Web Server

#### Menggunakan PHP Built-in Server (Development)

```bash
cd toko-inventori/public
php -S localhost:8000
```

Akses aplikasi di: `http://localhost:8000`

#### Menggunakan Apache

1. Copy folder `toko-inventori` ke direktori web server (htdocs/www)
2. Aktifkan mod_rewrite di Apache
3. Restart Apache
4. Akses aplikasi di: `http://localhost/toko-inventori/public`

#### Menggunakan Nginx

Konfigurasi nginx:

```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/toko-inventori/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## Deployment (Dokploy)

Proyek ini telah dikonfigurasi untuk langsung dapat di-deploy menggunakan **Dokploy** dengan mudah. 
Silakan baca panduan lengkapnya pada file: **[DEPLOY_DOKPLOY.md](DEPLOY_DOKPLOY.md)**

## Struktur Direktori

```
toko-inventori/
│
├── public/                 # Document root
│   ├── index.php          # Entry point
│   └── assets/css/        # CSS files
│
├── app/
│   ├── config/            # Konfigurasi database
│   ├── controllers/       # Controller files
│   ├── models/            # Model files
│   ├── views/             # View files
│   └── helpers/           # Helper functions
│
├── routes/                # Routing configuration
├── database/              # Database schema
└── .htaccess             # Apache configuration
```

## Penggunaan

### 1. Dashboard
- Lihat statistik ringkas (total barang, stok, penjualan, pembelian)
- Akses cepat ke fitur-fitur utama

### 2. Manajemen Barang
- **Tambah Barang**: Input nama, harga beli, harga jual, dan stok awal
- **Edit Barang**: Perbarui informasi barang
- **Hapus Barang**: Hapus data barang (akan menghapus transaksi terkait)

### 3. Pembelian
- Input pembelian barang
- Stok akan otomatis bertambah

### 4. Penjualan
- Input penjualan barang
- Validasi stok otomatis
- Stok akan otomatis berkurang

### 5. Laporan
- Filter berdasarkan periode tanggal
- Export/print laporan
- Lihat detail keuntungan

## Catatan Penting

- Pastikan PHP memiliki ekstensi PDO dan pdo_mysql
- Pastikan folder memiliki permission yang sesuai
- Backup database secara berkala
- Untuk production, ubah konfigurasi database dan aktifkan error handling yang proper

## Troubleshooting

### Error: Connection refused
- Pastikan MySQL service berjalan
- Cek konfigurasi database.php

### Error: 404 Not Found
- Pastikan mod_rewrite aktif (Apache)
- Cek file .htaccess
- Pastikan DocumentRoot mengarah ke folder public/

### Stok tidak terupdate
- Cek foreign key constraints
- Pastikan transaksi database berjalan dengan benar

## Lisensi

Free to use for educational purposes.

## Author

Sistem Inventori Toko v1.0


## Stok dan beberapa satuan per barang

Pada Tambah/Edit Barang, isi **Satuan & Harga** untuk setiap barang. Sediakan satuan dasar dengan nilai **1** (misalnya pcs atau botol). Nilai satuan lain adalah jumlah satuan dasar di dalam kemasan, bukan jumlah kemasan bertingkat:

| Satuan | Isi dalam satuan dasar | Harga |
| --- | ---: | --- |
| pcs | 1 | Harga per pcs |
| renteng | 6 | Harga per renteng |
| pack | 12 | Harga per pack |
| dus | 100 | Harga per dus |

Nilai ini khusus untuk masing-masing barang: pack pada barang lain dapat berisi 6 atau 10 pcs. Stok awal dan stok pada daftar/laporan selalu dalam satuan dasar. Jangan menjumlahkan stok pcs dan pack sebagai stok terpisah.

Pembelian 2 dus isi 100 menambah 200 pcs. Penjualan campuran 1 dus + 2 pack + 1 renteng + 3 pcs mengurangi 133 pcs; dari stok 300 pcs tersisa 167 pcs. Harga mengikuti satuan yang dipilih, sedangkan modal batch dihitung per satuan dasar lalu dikonversi ke satuan penjualan. Edit/hapus pembelian menggunakan konversi yang tersimpan pada transaksi, sehingga perubahan isi kemasan tidak mengubah jumlah stok yang dikembalikan.

Pengujian lokal:

```sh
node --test tests/*.test.cjs
php tests/penjualan-save.php
```

Uji PHP memerlukan koneksi PostgreSQL dari konfigurasi aplikasi dan memakai tabel serta sequence sementara, tanpa mengubah data toko.
