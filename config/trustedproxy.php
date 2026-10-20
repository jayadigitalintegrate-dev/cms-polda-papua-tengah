<?php

use App\Support\TrustedProxyList;
use Illuminate\Support\Env;

/*
|--------------------------------------------------------------------------
| Trusted proxy (HIGH-01)
|--------------------------------------------------------------------------
|
| Dibaca oleh Illuminate\Http\Middleware\TrustProxies (config
| 'trustedproxy.proxies'). Nilai dihitung saat konfigurasi dibangun sehingga
| kompatibel dengan config:cache; security:preflight tidak membaca env mentah.
|
| TRUSTED_PROXIES      Literal IP/CIDR dipisahkan koma. Kosong = tidak ada proxy.
| TRUSTED_PROXY_MODE   direct | proxied. Hanya untuk pemeriksaan preflight;
|                      tidak mengubah trust saat runtime.
|
*/

return (static function (): array {
    $parsed = TrustedProxyList::parse(env('TRUSTED_PROXIES'));
    // Nilai mentah (K1): env() memetakan literal null/(null) menjadi null PHP
    // sehingga tidak dapat dibedakan dari "tidak disetel".
    $mode = TrustedProxyList::resolveMode(Env::getRepository()->get('TRUSTED_PROXY_MODE'));

    return [

        // Selalu array. [] = tidak ada proxy tepercaya; null tidak pernah
        // dikembalikan karena memicu fallback trust-all TrustProxies.
        'proxies' => $parsed['proxies'],

        // Posisi dan kode alasan segmen yang ditolak (tanpa nilai). Bila tidak
        // kosong, 'proxies' = [] (tidak ada aktivasi sebagian).
        'rejected' => $parsed['rejected'],

        // Mode efektif: 'direct' bila tidak disetel atau tidak valid.
        'mode' => $mode['mode'],

        // default (tidak disetel) | declared (direct/proxied) | invalid.
        'mode_source' => $mode['mode_source'],

    ];
})();
