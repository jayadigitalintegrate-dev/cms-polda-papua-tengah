<?php

namespace App\Support;

/**
 * HIGH-01: validasi daftar trusted proxy (varian kanonik). Murni, tanpa I/O;
 * dipakai oleh config/trustedproxy.php dan security:preflight.
 *
 * - Hanya literal IPv4/IPv6 dan CIDR numerik. IPv4 wajib desimal kanonik
 *   (tanpa nol di depan, bentuk singkat, oktal, atau heksadesimal). Parser
 *   ditulis sendiri agar hasilnya identik di Windows dan Linux.
 * - Prefix IPv4 1-32 dan IPv6 1-128; /0 dan host bits tidak nol ditolak.
 * - Keluaran kanonik: IPv6 lowercase terkompresi (RFC 5952); /32 dan /128
 *   menjadi literal host; duplikat dibuang, kemunculan pertama dipertahankan.
 * - IPv4-mapped IPv6 ditolak pada versi awal.
 * - Fail-closed: bila satu segmen ditolak, proxies = [] (tanpa aktivasi
 *   sebagian). 'rejected' hanya memuat posisi dan kode alasan, tidak pernah
 *   nilai input atau pesan exception.
 */
final class TrustedProxyList
{
    public const INVALID_TYPE = 'INVALID_TYPE';
    public const EMPTY_SEGMENT = 'EMPTY_SEGMENT';
    public const CONTROL_CHARACTER = 'CONTROL_CHARACTER';
    public const FORBIDDEN_ALIAS = 'FORBIDDEN_ALIAS';
    public const INVALID_ADDRESS = 'INVALID_ADDRESS';
    public const INVALID_PREFIX = 'INVALID_PREFIX';
    public const ZERO_PREFIX = 'ZERO_PREFIX';
    public const HOST_BITS_SET = 'HOST_BITS_SET';
    public const MAPPED_ADDRESS_UNSUPPORTED = 'MAPPED_ADDRESS_UNSUPPORTED';

    public const REASONS = [
        self::INVALID_TYPE,
        self::EMPTY_SEGMENT,
        self::CONTROL_CHARACTER,
        self::FORBIDDEN_ALIAS,
        self::INVALID_ADDRESS,
        self::INVALID_PREFIX,
        self::ZERO_PREFIX,
        self::HOST_BITS_SET,
        self::MAPPED_ADDRESS_UNSUPPORTED,
    ];

    public const MODES = ['direct', 'proxied'];

    /** Kata kunci yang diperluas Laravel/Symfony menjadi trust luas (tanpa membedakan huruf). */
    private const FORBIDDEN = ['*', '**', 'remote_addr', 'private_subnets', 'private_ranges'];

    private const IPV4_OCTET = '(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])';

    /**
     * Posisi 1-based per segmen; posisi 0 berarti seluruh input (INVALID_TYPE).
     *
     * @return array{proxies: list<string>, rejected: list<array{position: int, reason: string}>}
     */
    public static function parse(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return ['proxies' => [], 'rejected' => []];
        }

        if (! is_string($raw)) {
            return ['proxies' => [], 'rejected' => [['position' => 0, 'reason' => self::INVALID_TYPE]]];
        }

        // K3 opsi B: seluruhnya spasi (U+0020) = tidak dikonfigurasi. Tab,
        // newline, dan segmen kosong akibat koma tetap ditolak di bawah.
        if (trim($raw, ' ') === '') {
            return ['proxies' => [], 'rejected' => []];
        }

        $accepted = [];
        $rejected = [];

        foreach (explode(',', $raw) as $index => $segment) {
            $result = self::normalizeEntry($segment);

            if ($result['reason'] === null) {
                $accepted[] = $result['entry'];
            } else {
                $rejected[] = ['position' => $index + 1, 'reason' => $result['reason']];
            }
        }

        return [
            'proxies' => $rejected === [] ? array_values(array_unique($accepted)) : [],
            'rejected' => $rejected,
        ];
    }

    /**
     * Bentuk kanonik satu segmen, atau kode alasan penolakan. Urutan
     * pemeriksaan tetap sehingga satu segmen selalu menghasilkan satu kode.
     *
     * @return array{entry: ?string, reason: ?string}
     */
    public static function normalizeEntry(string $segment): array
    {
        if (preg_match('/[\x00-\x1F\x7F]/', $segment) === 1) {
            return self::reject(self::CONTROL_CHARACTER);
        }

        $entry = trim($segment, ' ');

        if ($entry === '') {
            return self::reject(self::EMPTY_SEGMENT);
        }

        if (in_array(strtolower($entry), self::FORBIDDEN, true)) {
            return self::reject(self::FORBIDDEN_ALIAS);
        }

        $parts = explode('/', $entry, 2);
        $ipv6 = str_contains($parts[0], ':');
        $binary = $ipv6 ? self::parseIpv6($parts[0]) : self::parseIpv4($parts[0]);

        if ($binary === null) {
            return self::reject(self::INVALID_ADDRESS);
        }

        if ($ipv6 && str_starts_with($binary, str_repeat("\0", 10)."\xFF\xFF")) {
            return self::reject(self::MAPPED_ADDRESS_UNSUPPORTED);
        }

        $max = $ipv6 ? 128 : 32;
        $prefix = $max;

        if (count($parts) === 2) {
            if ($parts[1] === '0') {
                return self::reject(self::ZERO_PREFIX);
            }

            if (preg_match('/\A[1-9][0-9]{0,2}\z/', $parts[1]) !== 1 || (int) $parts[1] > $max) {
                return self::reject(self::INVALID_PREFIX);
            }

            $prefix = (int) $parts[1];
        }

        if (self::hostBitsSet($binary, $prefix)) {
            return self::reject(self::HOST_BITS_SET);
        }

        $address = $ipv6 ? self::formatIpv6($binary) : implode('.', unpack('C4', $binary));

        return ['entry' => $prefix === $max ? $address : $address.'/'.$prefix, 'reason' => null];
    }

    /** True bila entri sudah berbentuk kanonik dan valid (dipakai preflight). */
    public static function isCanonicalEntry(string $entry): bool
    {
        $result = self::normalizeEntry($entry);

        return $result['reason'] === null && $result['entry'] === $entry;
    }

    /** True bila metadata 'rejected' berbentuk list posisi + kode alasan yang dikenal. */
    public static function isRejectedMetadata(mixed $rejected): bool
    {
        if (! is_array($rejected) || ! array_is_list($rejected)) {
            return false;
        }

        foreach ($rejected as $item) {
            if (! is_array($item)
                || array_keys($item) !== ['position', 'reason']
                || ! is_int($item['position'])
                || $item['position'] < 0
                || ! in_array($item['reason'], self::REASONS, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Status TRUSTED_PROXY_MODE (keputusan T-1). Mode efektif 'direct' bila
     * tidak disetel atau tidak valid; mode_source membedakan ketiganya.
     *
     * $raw adalah nilai mentah repository environment (K1), bukan hasil env():
     * null hanya berarti tidak disetel. Literal null/(null)/empty, string
     * kosong, karakter kontrol, dan kapitalisasi lain menjadi 'invalid'.
     * Trim hanya U+0020 (K1-a opsi ii).
     *
     * @return array{mode: string, mode_source: string}
     */
    public static function resolveMode(mixed $raw): array
    {
        if ($raw === null) {
            return ['mode' => 'direct', 'mode_source' => 'default'];
        }

        if (is_string($raw)
            && preg_match('/[\x00-\x1F\x7F]/', $raw) !== 1
            && in_array(trim($raw, ' '), self::MODES, true)) {
            return ['mode' => trim($raw, ' '), 'mode_source' => 'declared'];
        }

        return ['mode' => 'direct', 'mode_source' => 'invalid'];
    }

    /** @return array{entry: null, reason: string} */
    private static function reject(string $reason): array
    {
        return ['entry' => null, 'reason' => $reason];
    }

    /** 4 byte network order, atau null bila bukan IPv4 desimal kanonik. */
    private static function parseIpv4(string $address): ?string
    {
        $octet = self::IPV4_OCTET;

        if (preg_match("/\\A{$octet}(?:\\.{$octet}){3}\\z/", $address) !== 1) {
            return null;
        }

        return pack('C4', ...array_map('intval', explode('.', $address)));
    }

    /** 16 byte network order, atau null bila bukan literal IPv6 yang valid (tanpa zone). */
    private static function parseIpv6(string $address): ?string
    {
        if (preg_match('/\A[0-9A-Fa-f:.]+\z/', $address) !== 1) {
            return null;
        }

        $embedded = null;

        if (str_contains($address, '.')) {
            $colon = strrpos($address, ':');
            $embedded = self::parseIpv4(substr($address, $colon + 1));

            if ($embedded === null) {
                return null;
            }

            // Dua grup pengganti untuk 32 bit IPv4 di akhir alamat.
            $address = substr($address, 0, $colon + 1).'0:0';
        }

        $halves = explode('::', $address);

        if (count($halves) > 2) {
            return null;
        }

        $split = fn (string $part) => $part === '' ? [] : explode(':', $part);
        $head = $split($halves[0]);
        $tail = count($halves) === 2 ? $split($halves[1]) : [];

        foreach ([...$head, ...$tail] as $group) {
            if (preg_match('/\A[0-9A-Fa-f]{1,4}\z/', $group) !== 1) {
                return null;
            }
        }

        $count = count($head) + count($tail);

        if (count($halves) === 2) {
            if ($count > 7) {
                return null;
            }

            $groups = [...$head, ...array_fill(0, 8 - $count, '0'), ...$tail];
        } elseif ($count === 8) {
            $groups = $head;
        } else {
            return null;
        }

        $binary = pack('n8', ...array_map('hexdec', $groups));

        return $embedded === null ? $binary : substr($binary, 0, 12).$embedded;
    }

    /** RFC 5952: lowercase, tanpa nol di depan, '::' untuk deret nol terpanjang (minimal 2 grup). */
    private static function formatIpv6(string $binary): string
    {
        $groups = array_values(unpack('n8', $binary));
        $bestStart = -1;
        $bestLength = 0;

        for ($i = 0; $i < 8; $i++) {
            if ($groups[$i] !== 0) {
                continue;
            }

            $start = $i;

            while ($i < 8 && $groups[$i] === 0) {
                $i++;
            }

            if ($i - $start > $bestLength) {
                $bestStart = $start;
                $bestLength = $i - $start;
            }
        }

        $hex = array_map('dechex', $groups);

        if ($bestLength < 2) {
            return implode(':', $hex);
        }

        return implode(':', array_slice($hex, 0, $bestStart)).'::'.implode(':', array_slice($hex, $bestStart + $bestLength));
    }

    private static function hostBitsSet(string $binary, int $prefix): bool
    {
        for ($i = 0, $length = strlen($binary); $i < $length; $i++) {
            $bits = max(0, min(8, $prefix - $i * 8));
            $hostMask = 0xFF >> $bits;

            if ((ord($binary[$i]) & $hostMask) !== 0) {
                return true;
            }
        }

        return false;
    }
}
