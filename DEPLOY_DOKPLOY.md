# 🚀 Panduan Deployment Dokploy

Proyek ini telah dikonfigurasi dan dioptimalkan agar dapat berjalan dengan mulus di platform **Dokploy** menggunakan metode deployment **Application (Dockerfile)**.

## 📋 Prasyarat
1. Anda sudah memiliki server/VPS dengan **Dokploy** yang sudah terinstall.
2. Kode proyek ini sudah di-push ke repository GitHub Anda (bisa public atau private).
3. Anda sudah menyiapkan database (baik MySQL/MariaDB atau PostgreSQL). 

## 🛠 Langkah-langkah Deployment di Dokploy

### 1. Buat Aplikasi Baru di Dokploy
1. Buka dashboard Dokploy Anda.
2. Navigasi ke menu **Applications** dan klik **Create Application**.
3. Beri nama aplikasi Anda (misalnya: `inventoris-toko`).

### 2. Hubungkan ke Repository GitHub
1. Pada tab **General**, pilih sumber (Source) dari **GitHub**.
2. Pilih repository `inventoris_toko` Anda.
3. Pilih branch yang akan di-deploy (contoh: `main`).
4. Klik **Save**.

### 3. Konfigurasi Build Type (Penting!)
Dokploy harus tahu bagaimana membangun aplikasi ini. Karena kita sudah menyediakan file `Dockerfile` khusus:
1. Pindah ke tab **Build**.
2. Pada opsi **Build Type**, pilih **Dockerfile**.
3. Pastikan **Dockerfile Path** terisi dengan `/Dockerfile` atau dikosongkan (default).
4. Klik **Save**.

### 4. Atur Environment Variables (.env)
Aplikasi PHP ini membutuhkan konfigurasi database agar dapat berjalan:
1. Pindah ke tab **Environment**.
2. Masukkan konfigurasi `.env` production Anda. Contoh:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-inventori-anda.com
TIMEZONE=Asia/Jakarta

DB_HOST=<ip_atau_nama_container_database>
DB_PORT=5432
DB_NAME=toko_inventori
DB_USER=<user_database>
DB_PASS=<password_database>

# Dokploy default port routing (jangan diubah)
PORT=80
```
3. Klik **Save**.

### 5. Atur Domain (Opsional tapi Direkomendasikan)
1. Pindah ke tab **Domains**.
2. Tambahkan domain atau subdomain yang ingin Anda gunakan (contoh: `inventori.domain-anda.com`).
3. Port internal biarkan ke **80** (sesuai konfigurasi Nginx pada `Dockerfile`).
4. Aktifkan opsi **HTTPS** jika domain sudah di-pointing ke IP Dokploy Anda.
5. Klik **Save**.

### 6. Deploy Aplikasi
1. Kembali ke tab **General** atau tab **Deployments**.
2. Klik tombol **Deploy**.
3. Tunggu proses build (biasanya 1-3 menit). Anda bisa memantau prosesnya dengan mengklik tombol *View Logs*.

---

## 🎯 Setelah Berhasil Deploy

Jika aplikasi sudah berjalan (*Running*), Anda dapat mengakses domain yang sudah di-set tadi.

Gunakan kredensial default untuk login (pastikan untuk langsung mengubahnya setelah berhasil masuk!):
- **Username:** `admin`
- **Password:** `admin123`

### 💡 Troubleshooting
Jika terjadi *Error 502 Bad Gateway* atau aplikasi gagal terhubung ke database:
1. Cek tab **Logs** di Dokploy, periksa apakah ada error koneksi ke database.
2. Pastikan `DB_HOST`, `DB_PORT`, dan kredensial database di tab Environment sudah 100% benar.
3. Pastikan jaringan antara database dan aplikasi (jika database juga di-host di Dokploy) dapat saling berkomunikasi. Anda tidak perlu setup jaringan tambahan, Dokploy akan otomatis me-routing jika berada di VPS yang sama.

🎉 **Selesai! Aplikasi Inventori Toko Anda sekarang online via Dokploy.**

## Security patch NGINX — 22 November 2026

### Hasil audit dan perubahan

- Deployment memakai satu stage `php:8.2-fpm` dengan NGINX dari APT Debian, bukan image `nginx:*`, multi-stage build, atau frontend SPA terpisah. Tidak ada pipeline build JavaScript, Composer, atau konfigurasi Traefik di repository. `docker-compose.yml` membangun Dockerfile yang sama.
- Sebelumnya versi NGINX tidak dikunci. Versi container yang sudah berjalan hanya bisa diketahui dengan `nginx -v`; nomor versi Debian juga dapat membawa backport sehingga status patch sebelumnya tidak dapat disimpulkan dari Dockerfile saja.
- Sekarang paket dikunci ke **`nginx=1.30.7-1~trixie`** dari repository stable resmi `https://nginx.org/packages/debian/`. [Advisory resmi NGINX](https://nginx.org/en/security_advisories.html) menyatakan stable **1.30.7+** sudah memperbaiki CVE-2026-123634 dan kerentanan terkait.
- Base image dikunci ke `php:8.2-fpm-trixie@sha256:66247f3e15ad735951c06af08e8b4bedb20874ea9464039146f2f0fad0ca9616`. Saat audit, tag ini dan `php:8.2-fpm` memiliki digest yang sama (PHP 8.2.36 / Debian Trixie). Tidak ada pergantian versi mayor/minor PHP atau distribusi dibanding tag yang tersedia saat audit.
- Signing key resmi dikunci dengan SHA-256 dan APT memakai `signed-by` khusus untuk repository NGINX. Instalasi versi yang salah atau perubahan isi signing key akan menggagalkan build. Tidak ada fallback ke paket Debian lama.
- Build memeriksa `nginx -v` tepat `1.30.7`, lalu menjalankan `nginx -t` pada salinan konfigurasi dengan port 80. Salinan ditempatkan di `/etc/nginx/nginx-check.conf` agar `include fastcgi_params` tetap merujuk ke `/etc/nginx/fastcgi_params`; menaruh salinan di `/tmp` menyebabkan include tersebut gagal ditemukan. Template asli tetap memakai `$PORT` untuk diproses `start.sh` saat startup.
- `nginx.conf` menetapkan `user nobody nogroup` dan `error_log stderr` agar default worker dan error log paket Debian sebelumnya tetap sama setelah beralih ke paket upstream. Default lokasi temporary files mengikuti paket upstream; tidak ada volume aplikasi di lokasi tersebut.

### Konfigurasi yang dipertahankan

| Area | Hasil audit |
| --- | --- |
| Routing dan static files | Document root `/app/public`; `try_files $uri $uri/ /index.php?$query_string` tetap meneruskan route PHP dan query string. Bukan fallback SPA ke `index.html`. |
| Port dan proxy | HTTP internal `${PORT:-80}`, Compose `8000:80`, FastCGI `127.0.0.1:9000`. TLS tetap diterminasi Traefik; konfigurasi Traefik tidak diubah. |
| Jalur advisory | Tidak ada `resolver`, HTTP/2, HTTP/3/QUIC, TLS, `alias`, `proxy_pass`, mail, atau stream aktif pada konfigurasi container. `root` + `try_files` tidak memakai kombinasi `alias` yang disebut advisory. |
| Cache, compression, headers | Konfigurasi container tidak menetapkan cache header khusus, gzip, atau security header. Setting tersebut ada pada contoh bare-metal `config/nginx-site.conf`, yang tidak di-include oleh konfigurasi container; contoh tetap dipertahankan. |
| Konfigurasi contoh | `README.md` dan `config/nginx-site.conf` memakai socket PHP host, bukan runtime Docker. `listen ... ssl http2` pada contoh HTTPS adalah sintaks lama/deprecated, tetapi seluruh blok itu dikomentari dan tidak aktif. Jangan mengaktifkannya untuk deployment Dokploy ini. |
| Environment dan healthcheck | Substitusi `PORT`, konfigurasi PHP-FPM, healthcheck `/login`, restart policy, dan volume `app_logs` tidak diubah. Database, secrets, dan environment produksi tidak diubah. |

Catatan keamanan yang sudah ada: blok PHP runtime meneruskan semua URL berakhiran `.php` tanpa `try_files $uri =404`; blok penolakan dotfile berada setelah regex PHP. Karena itu penolakan dotfile tidak mencakup dotfile berakhiran `.php`. Endpoint PHP langsung seperti `public/env.php` dan `public/migrate.php` juga tetap mengikuti aturan PHP tersebut. Hardening akses endpoint ini perlu audit aplikasi tersendiri; patch ini tidak mengubah akses atau business logic backend.

### Validasi yang dilakukan

- Registry Docker: manifest dan digest base image diperiksa; tersedia `linux/amd64` dan `linux/arm64/v8`.
- Repository APT: paket `1.30.7-1~trixie` tersedia untuk `amd64` dan `arm64`; signature `InRelease` diverifikasi dengan signing key resmi, checksum index dicocokkan, lalu kedua `.deb` diunduh dan SHA-256 diverifikasi. Paket memuat `/usr/sbin/nginx`, `/etc/nginx/mime.types`, dan `/etc/nginx/fastcgi_params` yang diperlukan aplikasi.
- `bash -n start.sh`, `docker compose --env-file .env.example config --quiet`, dan `git diff --check` lolos. Substitusi template juga diperiksa secara statis untuk port default dan port alternatif.
- **Build image sudah dicoba, tetapi gagal terhubung ke daemon Docker sebelum proses build dimulai.** Eksekusi `nginx -t`, pemeriksaan versi runtime, HTTP routing, assets, dan healthcheck belum dijalankan. Pemeriksaan dalam Dockerfile baru akan dieksekusi saat build sesungguhnya.
- Log build Dokploy yang kemudian diberikan menunjukkan instalasi paket NGINX `1.30.7-1~trixie` dan pemeriksaan versi berhasil, tetapi `nginx -t` gagal karena salinan konfigurasi di `/tmp` mencari `/tmp/fastcgi_params`. Lokasi salinan sudah diperbaiki ke `/etc/nginx`; keberhasilan build lengkap setelah perbaikan masih perlu diverifikasi.

### Rebuild dan redeploy

1. Commit dan push perubahan melalui alur repository yang biasa dipakai.
2. Uji di environment staging dengan Docker daemon aktif. Untuk build lokal:

   ```sh
   docker build --pull --no-cache -t inventoris-toko:nginx-1.30.7 .
   docker run --rm --entrypoint nginx inventoris-toko:nginx-1.30.7 -v
   ```

3. Di aplikasi Dokploy yang sudah ada, tetap gunakan build type **Dockerfile** dan port internal **80**. Pilih commit patch, rebuild tanpa cache melalui opsi build yang tersedia, lalu jalankan redeploy. Restart container lama saja tidak memasang patch. Jangan mengubah environment, database, volume, domain, atau Traefik.
4. Tunggu rollout selesai. Pada Docker Swarm, cek `docker service ps <nama-service>`, pastikan task baru sehat dan tidak ada task versi lama yang masih melayani traffic. Jalankan verifikasi berikut pada setiap container/task baru di node tempat task berjalan:

   ```sh
   docker ps --filter label=com.docker.swarm.service.name=<nama-service>
   CONTAINER_ID=<id-container-task-baru>
   docker exec "$CONTAINER_ID" nginx -v
   docker exec "$CONTAINER_ID" nginx -t
   docker exec "$CONTAINER_ID" dpkg-query -W nginx
   docker exec "$CONTAINER_ID" sh -c 'curl -fsS "http://127.0.0.1:${PORT:-80}/login" >/dev/null'
   docker inspect --format '{{json .State.Health}}' "$CONTAINER_ID"
   ```

   Hasil yang diharapkan: `nginx/1.30.7`, paket `1.30.7-1~trixie`, konfigurasi valid, `/login` berhasil, dan status health `healthy` setelah masa startup.

5. Lewat domain aplikasi, periksa `/login`, redirect `/` tanpa login, route setelah login, query string, serta `/assets/js/money.js` dan `/assets/css/tailwind.css`. Bandingkan status, isi, dan header dengan deployment sebelumnya. Untuk Compose lokal, gunakan alamat yang sama seperti sebelumnya, `http://localhost:8000`. Pemeriksaan HTTP aplikasi dilakukan dengan database staging; jangan menghubungkan pengujian lokal ke database produksi.

Risiko kompatibilitas: perpindahan paket Debian ke upstream mengubah default internal paket dan kumpulan MIME; identitas worker serta error log sudah dipertahankan, tetapi pengujian runtime tetap diperlukan. Paket upstream yang diverifikasi hanya mendukung `amd64`/`arm64`; arsitektur VPS belum diketahui. Pin digest berlaku untuk base image, sementara dependency APT lain belum memakai snapshot sehingga keseluruhan image belum bit-for-bit reproducible. Rotasi signing key, penghapusan versi paket dari repository, atau patch NGINX berikutnya memerlukan pembaruan pin secara eksplisit.
