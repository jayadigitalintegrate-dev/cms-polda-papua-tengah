# Security Operations Notes — CMS Polda Papua Tengah

Catatan ini memisahkan proteksi yang ada di level aplikasi (repository)
dari proteksi yang harus disediakan infrastruktur production.

## Proteksi di level aplikasi

| Area | Implementasi | Lokasi |
|---|---|---|
| Rate limit API baca publik | `public-read`: 300 request/menit per IP | `AppServiceProvider`, `routes/api.php` |
| Rate limit pengaduan | `public-complaints`: 5/menit dan 20/jam per IP | `AppServiceProvider`, `routes/api.php` |
| Rate limit permohonan PPID | `public-ppid`: 5/menit dan 20/jam per IP | `AppServiceProvider`, `routes/api.php` |
| Rate limit lupa password | `throttle:6,1` + throttle bawaan password broker (60 detik per email) | `routes/auth.php`, `config/auth.php` |
| Rate limit login | 5 percobaan per email+IP (Breeze `LoginRequest`) | `app/Http/Requests/Auth/LoginRequest.php` |
| CORS | Origin dari `CORS_ALLOWED_ORIGINS` (default: Vite lokal + GitHub Pages), method GET/POST | `config/cors.php` |
| Security headers | nosniff, X-Frame-Options, Referrer-Policy, Permissions-Policy; HSTS hanya production + HTTPS | `app/Http/Middleware/SecurityHeaders.php` |
| Debug di production | `APP_ENV=production` selalu mematikan debug | `config/app.php` |

Rate limiter memakai cache store aplikasi (`CACHE_STORE`). Jika production
menjalankan beberapa server, cache store harus dipakai bersama (mis. Redis/DB)
agar batas berlaku konsisten.

## Batas yang TIDAK dapat diselesaikan dari repository

Rate limiting aplikasi bukan proteksi DDoS. Request flood tetap mencapai
web server dan PHP sebelum ditolak dengan HTTP 429.

| ID | Kebutuhan infrastruktur | Status |
|---|---|---|
| S-16 | Proteksi DDoS/volumetrik di depan server (CDN/WAF, rate limit Nginx, firewall) | NOT VERIFIED — INFRASTRUCTURE ACTION REQUIRED |
| S-08 | Pemindaian malware untuk file upload (mis. ClamAV) | NOT AVAILABLE — INFRASTRUCTURE ACTION REQUIRED |
| S-08 | Web server menolak eksekusi script di `public/storage` | NOT VERIFIED |
| S-11 | Security headers untuk file statis (`/storage`, `/build`) yang dilayani langsung web server | NOT VERIFIED |
| S-11 | Content-Security-Policy | FOLLOW-UP — perlu audit script/style/font (Vite, Alpine, inline style) |
| S-19 | Backup database + `storage/app/public` terjadwal, disimpan di luar server | NOT AVAILABLE — tidak ada script/dokumentasi backup di repository |
| S-19 | Prosedur restore yang pernah diuji | NOT AVAILABLE |
| S-20 | HTTPS, `SESSION_SECURE_COOKIE=true`, `APP_URL` domain production, `expose_php=Off` | NOT VERIFIED — konfigurasi server/.env production |
