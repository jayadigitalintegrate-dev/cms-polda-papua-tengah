# Security Deployment Checklist — CMS Polda Papua Tengah

Checklist ini wajib dipenuhi sebelum CMS dibuka untuk publik. Centang setiap
butir dan catat bukti (output perintah / screenshot / nama petugas).

Langkah eksekusi ada di [SECURITY-DEPLOYMENT-RUNBOOK.md](SECURITY-DEPLOYMENT-RUNBOOK.md).

## 0. Gate otomatis

- [ ] `php artisan test tests/Feature/Security` — semua PASS (di mesin build/CI)
- [ ] `php artisan security:preflight --with-db` di server production — **0 FAIL**
- [ ] Semua WARN dari preflight sudah ditinjau dan diterima/diperbaiki

## 1. Aplikasi (`.env` production)

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` dibuat baru di server (`php artisan key:generate`), tidak disalin dari lokal
- [ ] `APP_URL` = URL HTTPS domain CMS production
- [ ] `LOG_LEVEL=warning` (atau lebih tinggi)
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `SESSION_ENCRYPT=true`
- [ ] `CACHE_STORE` persisten dan dipakai bersama semua server (database/redis), bukan `array`
- [ ] `CORS_ALLOWED_ORIGINS` = hanya domain website production (tanpa localhost)
- [ ] `.env` tidak ter-commit dan permission file hanya untuk user aplikasi
- [ ] `php artisan config:cache` dan `php artisan route:cache` dijalankan setelah `.env` final

## 2. Database

- [ ] Aplikasi memakai user MySQL khusus, **bukan** `root`
- [ ] User aplikasi hanya punya `SELECT, INSERT, UPDATE, DELETE` (+ hak migrasi saat deploy) pada schema CMS
- [ ] User aplikasi **tidak** punya `FILE`, `SUPER`, `SHUTDOWN`, `PROCESS`, `GRANT OPTION`
- [ ] Password user aplikasi kuat dan hanya ada di `.env` production
- [ ] Akun `root` MySQL memiliki password
- [ ] `bind-address` dan `mysqlx-bind-address` = `127.0.0.1` (atau jaringan privat)
- [ ] Port 3306/33060 tidak terbuka ke internet
- [ ] `secure_file_priv` dibatasi, `local_infile=OFF`

## 3. PHP (php.ini production)

- [ ] `expose_php = Off`
- [ ] `display_errors = Off`, `log_errors = On`
- [ ] `upload_max_filesize` ≈ 20M, `post_max_size` ≈ 25M (sesuai validasi aplikasi 5–10 MB)
- [ ] `max_execution_time` 30–60, `max_input_time` 60
- [ ] `session.use_strict_mode = 1`
- [ ] OPcache aktif

## 4. Web server

- [ ] Document root = folder `public/` CMS (bukan root project)
- [ ] `ServerTokens Prod` / `server_tokens off`, tanpa signature versi
- [ ] Directory listing nonaktif
- [ ] Eksekusi PHP/CGI ditolak di `/storage` (lihat contoh di runbook)
- [ ] Batas body request ≈ 25M
- [ ] Timeout baca/kirim wajar; proteksi slow request aktif
- [ ] Security headers juga dikirim untuk file statis (`/storage`, `/build`)
- [ ] `php artisan serve` **tidak** dipakai di production

## 5. TLS / HTTPS

- [ ] Sertifikat valid untuk domain CMS
- [ ] HTTP → HTTPS redirect
- [ ] Hanya TLS 1.2/1.3
- [ ] HSTS terkirim (otomatis oleh aplikasi bila production + HTTPS) — verifikasi dengan `curl -I`

## 6. Jaringan & DDoS

- [ ] CDN/WAF di depan CMS (mis. Cloudflare atau setara)
- [ ] Rate limit di edge/web server untuk `/api/*` dan `/login`
- [ ] Firewall server: hanya 80/443 (dan SSH dari IP admin) terbuka
- [ ] Panel admin CMS dipertimbangkan dibatasi IP/VPN

## 7. Backup & restore

- [ ] Backup database terjadwal (harian minimum)
- [ ] Backup `storage/app/public` terjadwal
- [ ] Backup disimpan di luar server (off-site) dan terenkripsi
- [ ] Retensi terdokumentasi (mis. 7 harian, 4 mingguan, 3 bulanan)
- [ ] Restore pernah diuji ke server non-production dan hasilnya dicatat

## 8. Keputusan bisnis yang masih terbuka (bukan blocker teknis)

- [ ] F-07: permission matrix role `operator` / `supervisi` diputuskan
- [ ] F-08: field biodata pejabat yang boleh tampil publik diputuskan
