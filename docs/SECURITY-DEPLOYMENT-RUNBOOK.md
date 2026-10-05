# Security Deployment Runbook — CMS Polda Papua Tengah

Runbook ini dipakai saat deployment pertama dan setiap rilis berikutnya.
Semua contoh konfigurasi server di bawah adalah **contoh** dan harus
disesuaikan serta disetujui sebelum diterapkan di server production.

Checklist go-live: [SECURITY-DEPLOYMENT-CHECKLIST.md](SECURITY-DEPLOYMENT-CHECKLIST.md)

---

## 1. Sebelum deployment (mesin build / CI)

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan test tests/Feature/Security
```

Lanjut hanya bila semua security test PASS.

## 2. Siapkan `.env` production

1. Salin `.env.example` menjadi `.env` **di server**, jangan menyalin `.env` lokal.
2. Isi nilai sesuai bagian "Production security baseline" di `.env.example`.
3. Buat key baru: `php artisan key:generate`.
4. Simpan password database hanya di `.env` server (bukan di repository, chat, atau tiket).
5. Batasi permission: `.env` hanya dapat dibaca user aplikasi.

## 3. Database (dilakukan DBA / admin server)

Contoh user least-privilege (sesuaikan nama dan host):

```sql
CREATE USER 'cms_app'@'localhost' IDENTIFIED BY '<password-kuat>';
GRANT SELECT, INSERT, UPDATE, DELETE ON cms_polda_papua_tengah.* TO 'cms_app'@'localhost';
```

Hak `CREATE, ALTER, INDEX, DROP, REFERENCES` hanya diberikan sementara saat
menjalankan migrasi, atau gunakan user migrasi terpisah.

Contoh `my.ini` / `my.cnf`:

```ini
[mysqld]
bind-address        = 127.0.0.1
mysqlx-bind-address = 127.0.0.1
secure_file_priv    = /var/lib/mysql-files
local_infile        = 0
```

## 4. Deploy aplikasi

```bash
php artisan down
git pull                      # atau ekstrak artefak rilis
composer install --no-dev --optimize-autoloader
php artisan migrate --force   # hanya bila rilis berisi migrasi; backup dulu (bagian 8)
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

## 5. Web server

### Nginx (contoh)

```nginx
server_tokens off;
client_max_body_size 25m;
client_body_timeout 15s;
client_header_timeout 15s;

limit_req_zone $binary_remote_addr zone=cms_api:10m rate=10r/s;

server {
    listen 443 ssl http2;
    server_name cms.example.go.id;
    root /var/www/cms/public;

    ssl_protocols TLSv1.2 TLSv1.3;

    add_header X-Content-Type-Options nosniff always;
    add_header X-Frame-Options SAMEORIGIN always;
    add_header Referrer-Policy strict-origin-when-cross-origin always;

    autoindex off;

    # Jangan pernah mengeksekusi script dari folder upload.
    location ~* ^/storage/.*\.(php|phtml|phar|php\d|cgi|pl|py|sh)$ {
        deny all;
    }

    location /api/ {
        limit_req zone=cms_api burst=20 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

server {
    listen 80;
    server_name cms.example.go.id;
    return 301 https://$host$request_uri;
}
```

### Apache (contoh)

```apache
ServerTokens Prod
ServerSignature Off
LimitRequestBody 26214400

<Directory "/var/www/cms/public">
    Options -Indexes -ExecCGI -Includes +FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>

<Directory "/var/www/cms/storage/app/public">
    Options -Indexes -ExecCGI -Includes
    <FilesMatch "\.(php|phtml|phar|php\d|cgi|pl|py|sh)$">
        Require all denied
    </FilesMatch>
    php_admin_flag engine off
</Directory>
```

## 6. Verifikasi setelah deployment

Semua perintah berikut read-only.

```bash
# Preflight aplikasi + database (harus 0 FAIL)
php artisan security:preflight --with-db

# Debug mati, error API tidak membocorkan detail internal
curl -s https://cms.example.go.id/api/tidak-ada
#   -> {"message":"The route ... could not be found."}  (tanpa "trace"/"file")

# Security headers + HSTS
curl -sI https://cms.example.go.id/login

# Rate limit header
curl -sI https://cms.example.go.id/api/news | grep -i x-ratelimit

# CORS: origin asing tidak mendapat Access-Control-Allow-Origin
curl -sI -H "Origin: https://evil.example" https://cms.example.go.id/api/news | grep -i access-control

# Port database tidak terbuka dari luar (jalankan dari mesin lain)
nc -zv cms.example.go.id 3306
```

Jangan melakukan load test atau DDoS test ke production tanpa persetujuan
tertulis dan koordinasi dengan penyedia hosting/CDN.

## 7. Backup

Minimal:

- Database: dump harian, contoh `mysqldump --single-transaction --routines --triggers cms_polda_papua_tengah`
  menggunakan user backup khusus (read-only + `LOCK TABLES`, `SHOW VIEW`, `TRIGGER`).
- Media: arsip `storage/app/public` harian.
- Enkripsi arsip sebelum dikirim ke lokasi off-site.
- Retensi: 7 harian, 4 mingguan, 3 bulanan (sesuaikan kebijakan instansi).
- Catat lokasi, jadwal, dan penanggung jawab backup.

## 8. Restore (uji berkala di server NON-production)

1. Siapkan server/database kosong non-production.
2. Dekripsi arsip backup terbaru.
3. Import dump database ke database uji.
4. Ekstrak media ke `storage/app/public`, jalankan `php artisan storage:link`.
5. Jalankan `php artisan security:preflight` dan buka beberapa halaman CMS/API.
6. Catat tanggal uji, durasi restore, dan hasilnya.

Jangan pernah menguji restore dengan menimpa database production.

## 9. Insiden / rollback

- Rollback kode: deploy ulang tag/commit sebelumnya, lalu `config:cache`/`route:cache`.
- Rollback data: restore dari backup terakhir yang valid (bagian 8) setelah persetujuan.
- Kredensial bocor: ganti password database, buat `APP_KEY` baru (seluruh sesi akan logout),
  ganti password akun admin, tinjau `storage/logs`.
