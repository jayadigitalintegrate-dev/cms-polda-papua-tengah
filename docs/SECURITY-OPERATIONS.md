# Security Operations Notes — CMS Polda Papua Tengah

Catatan ini memisahkan proteksi yang ada di level aplikasi (repository)
dari proteksi yang harus disediakan infrastruktur production.

Dokumen terkait:

- [SECURITY-DEPLOYMENT-CHECKLIST.md](SECURITY-DEPLOYMENT-CHECKLIST.md) — checklist go-live
- [SECURITY-DEPLOYMENT-RUNBOOK.md](SECURITY-DEPLOYMENT-RUNBOOK.md) — langkah deployment & verifikasi

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
| Validasi upload | `image`/`file` + `mimes` + `max`; nama file dibuat server (`->store()`), ekstensi dari isi file | Controller admin |

Rate limiter memakai cache store aplikasi (`CACHE_STORE`). Jika production
menjalankan beberapa server, cache store harus dipakai bersama (mis. Redis/DB)
agar batas berlaku konsisten.

## Tooling keamanan (read-only)

| Tool | Perintah | Keterangan |
|---|---|---|
| Preflight checker | `php artisan security:preflight` | Memeriksa konfigurasi app, session, CORS, rate limit, headers, php.ini, user database. Exit code 1 bila ada FAIL. Tidak mengubah apa pun dan tidak menampilkan secret. |
| Preflight + MySQL | `php artisan security:preflight --with-db` | Tambahan read-only: `SHOW GRANTS`, `bind_address`, `secure_file_priv`, `local_infile`. |
| Security regression tests | `php artisan test tests/Feature/Security` | Rate limit, CORS, headers, debug guard, upload, otorisasi, preflight. Berjalan di SQLite `:memory:` dan berhenti bila koneksi bukan SQLite in-memory. |

Di luar production, temuan khusus production dilaporkan sebagai WARN;
di production (`APP_ENV=production`) temuan yang sama menjadi FAIL.

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
| S-19 | Backup database + `storage/app/public` terjadwal, disimpan di luar server | PARTIAL — lihat audit infrastruktur |
| S-19 | Prosedur restore yang pernah diuji | NOT AVAILABLE |
| S-20 | HTTPS, `SESSION_SECURE_COOKIE=true`, `APP_URL` domain production, `expose_php=Off` | NOT VERIFIED — konfigurasi server/.env production |

## Hasil audit infrastruktur (2026-10-05, read-only)

Production belum tersedia: `cms.poldapapuatengah.go.id`, `poldapapuatengah.go.id`
dan `www.poldapapuatengah.go.id` belum ter-resolve (NXDOMAIN). Temuan di bawah
berlaku untuk workstation development (Laragon), bukan production.

| ID | Severity | Temuan (workstation dev) | Tindakan yang direkomendasikan |
|---|---|---|---|
| S-16.1 | HIGH | Belum ada WAF/CDN/proteksi volumetrik | Sediakan CDN/WAF + `limit_req`/`limit_conn` web server sebelum go-live |
| S-16.2 | HIGH | MySQL bind ke semua interface; firewall mengizinkan mysqld dari IP mana pun pada profil Private dan Public | Bind MySQL ke `127.0.0.1` atau batasi rule firewall |
| S-16.3 | MEDIUM | Firewall mengizinkan Apache dan mailpit dari IP mana pun pada profil Private dan Public | Hapus allow pada profil Public bila akses LAN tidak diperlukan |
| S-16.4 | LOW | `php artisan serve` single worker (khusus dev) | Production memakai php-fpm/Apache dengan worker memadai |
| S-19.1 | HIGH | Dump MySQL otomatis Laragon (~8 jam) hanya di disk yang sama dengan data | Salin backup ke lokasi di luar server |
| S-19.2 | MEDIUM | `storage/app/public` (media upload) tidak di-backup | Backup media bersama dump database |
| S-19.3 | MEDIUM | Dump berupa SQL plaintext (berisi hash password dan data pribadi) | Enkripsi backup dan batasi ACL folder |
| S-19.4 | MEDIUM | Belum ada prosedur restore yang teruji | Ikuti bagian restore pada runbook dan uji di non-production |
| S-19.5 | LOW | Retensi backup tidak terdokumentasi | Tetapkan retensi (mis. 7 harian, 4 mingguan) |
| S-20.1 | HIGH | Aplikasi memakai MySQL `root` dengan password kosong | User khusus least-privilege + password kuat |
| S-20.2 | MEDIUM | `secure_file_priv` kosong dan user aplikasi memiliki hak `FILE` | Batasi `secure_file_priv`; cabut `FILE` dari user aplikasi |
| S-20.3 | MEDIUM | PHP `post_max_size`/`upload_max_filesize` 2G, `max_execution_time` 36000 | Production: ±20M upload, 30–60 detik eksekusi |
| S-20.4 | MEDIUM | PHP `display_errors`/`expose_php` On, `session.use_strict_mode` 0 | Production php.ini: Off/Off/1 |
| S-20.5 | MEDIUM | Apache: versi terekspos, `Indexes`/`ExecCGI`/`Includes` aktif di document root | `ServerTokens Prod`, `ServerSignature Off`, `Options -Indexes -ExecCGI -Includes` |
| S-20.6 | MEDIUM | Eksekusi script di `public/storage` hanya dicegah validasi aplikasi | Web server menolak eksekusi script di `/storage` |
| S-20.7 | LOW | Belum ada security headers di level web server untuk file statis | Tambahkan header yang sama di web server |

Temuan ini **belum** dieksekusi. Perubahan database user, MySQL, firewall,
web server dan PHP memerlukan approval terpisah.
