<?php

namespace Tests\Unit;

use App\Support\TrustedProxyList;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * HIGH-01/D1: parser TRUSTED_PROXIES dan resolver TRUSTED_PROXY_MODE (murni,
 * tanpa aplikasi dan tanpa database).
 */
class TrustedProxyListTest extends TestCase
{
    /** C1 + K3: tidak dikonfigurasi menghasilkan daftar kosong, bukan null. */
    public function test_unconfigured_values_yield_empty_list(): void
    {
        foreach ([null, '', ' ', '   '] as $raw) {
            $this->assertSame(['proxies' => [], 'rejected' => []], TrustedProxyList::parse($raw), var_export($raw, true));
        }
    }

    public function test_non_string_input_is_rejected_as_a_whole(): void
    {
        foreach ([true, false, 0, 1.5, ['10.0.0.1']] as $raw) {
            $this->assertSame(
                ['proxies' => [], 'rejected' => [['position' => 0, 'reason' => TrustedProxyList::INVALID_TYPE]]],
                TrustedProxyList::parse($raw),
            );
        }
    }

    /** I2, I5, V2: entri valid diterima, dikanonikkan, duplikat dibuang. */
    public function test_valid_entries_are_accepted_in_canonical_form(): void
    {
        $result = TrustedProxyList::parse(' 198.51.100.10, 10.0.0.0/8 ,2001:DB8::/32,2001:db8:0:0::10/128,198.51.100.10/32,198.51.100.10');

        $this->assertSame([], $result['rejected']);
        $this->assertSame(['198.51.100.10', '10.0.0.0/8', '2001:db8::/32', '2001:db8::10'], $result['proxies']);
    }

    public static function rejectedEntries(): array
    {
        return [
            'wildcard' => ['*', TrustedProxyList::FORBIDDEN_ALIAS],
            'double wildcard' => ['**', TrustedProxyList::FORBIDDEN_ALIAS],
            'REMOTE_ADDR' => ['REMOTE_ADDR', TrustedProxyList::FORBIDDEN_ALIAS],
            'remote_addr' => ['remote_addr', TrustedProxyList::FORBIDDEN_ALIAS],
            'PRIVATE_SUBNETS' => ['PRIVATE_SUBNETS', TrustedProxyList::FORBIDDEN_ALIAS],
            'private_ranges' => ['private_ranges', TrustedProxyList::FORBIDDEN_ALIAS],
            'ipv4 /0' => ['10.0.0.0/0', TrustedProxyList::ZERO_PREFIX],
            'ipv6 /0' => ['::/0', TrustedProxyList::ZERO_PREFIX],
            'ipv4 /33' => ['1.2.3.4/33', TrustedProxyList::INVALID_PREFIX],
            'ipv6 /129' => ['2001:db8::/129', TrustedProxyList::INVALID_PREFIX],
            'prefix leading zero' => ['10.0.0.0/08', TrustedProxyList::INVALID_PREFIX],
            'prefix text' => ['1.2.3.4/x', TrustedProxyList::INVALID_PREFIX],
            'double prefix' => ['1.2.3.4/8/1', TrustedProxyList::INVALID_PREFIX],
            'zone id' => ['fe80::1%eth0', TrustedProxyList::INVALID_ADDRESS],
            'hostname' => ['abc', TrustedProxyList::INVALID_ADDRESS],
            'ipv4 leading zero' => ['010.0.0.1', TrustedProxyList::INVALID_ADDRESS],
            'ipv4 short form' => ['10.1', TrustedProxyList::INVALID_ADDRESS],
            'ipv4 hex' => ['0x0a.0.0.1', TrustedProxyList::INVALID_ADDRESS],
            'internal space' => ['10.0. 0.1', TrustedProxyList::INVALID_ADDRESS],
            'host bits ipv4' => ['10.0.0.1/8', TrustedProxyList::HOST_BITS_SET],
            'host bits ipv6' => ['2001:db8::1/64', TrustedProxyList::HOST_BITS_SET],
            'ipv4-mapped dotted' => ['::ffff:198.51.100.10', TrustedProxyList::MAPPED_ADDRESS_UNSUPPORTED],
            'ipv4-mapped hex' => ['::ffff:c633:640a', TrustedProxyList::MAPPED_ADDRESS_UNSUPPORTED],
            'ipv4-mapped cidr' => ['::ffff:0:0/96', TrustedProxyList::MAPPED_ADDRESS_UNSUPPORTED],
            'tab' => ["\t10.0.0.1", TrustedProxyList::CONTROL_CHARACTER],
            'newline' => ["10.0.0.1\n", TrustedProxyList::CONTROL_CHARACTER],
        ];
    }

    /** I1: setiap entri terlarang ditolak dengan kode alasan yang tepat. */
    #[DataProvider('rejectedEntries')]
    public function test_invalid_entry_is_rejected_with_reason(string $entry, string $reason): void
    {
        $this->assertSame(
            ['proxies' => [], 'rejected' => [['position' => 1, 'reason' => $reason]]],
            TrustedProxyList::parse($entry),
        );
    }

    /** I3: campuran valid dan tidak valid tidak pernah diaktifkan sebagian. */
    public function test_mixed_list_is_fail_closed(): void
    {
        $this->assertSame(
            ['proxies' => [], 'rejected' => [['position' => 2, 'reason' => TrustedProxyList::FORBIDDEN_ALIAS]]],
            TrustedProxyList::parse('198.51.100.10, *, 10.0.0.0/8'),
        );
    }

    /** I4: segmen kosong akibat koma tetap ditolak (berbeda dari spasi saja). */
    public function test_empty_segments_are_rejected(): void
    {
        $this->assertSame([['position' => 2, 'reason' => TrustedProxyList::EMPTY_SEGMENT]], TrustedProxyList::parse('198.51.100.10,')['rejected']);
        $this->assertSame([['position' => 2, 'reason' => TrustedProxyList::EMPTY_SEGMENT]], TrustedProxyList::parse('10.0.0.1, ,10.0.0.2')['rejected']);
        $this->assertSame([['position' => 1, 'reason' => TrustedProxyList::EMPTY_SEGMENT], ['position' => 2, 'reason' => TrustedProxyList::EMPTY_SEGMENT]], TrustedProxyList::parse(' , ')['rejected']);
        $this->assertSame([['position' => 1, 'reason' => TrustedProxyList::CONTROL_CHARACTER]], TrustedProxyList::parse(" \t ")['rejected']);
    }

    /** I6: metadata penolakan tidak memuat nilai input. */
    public function test_rejected_metadata_never_contains_input(): void
    {
        $result = TrustedProxyList::parse('10.0.0.1,PENANDA-RAHASIA-123');

        $this->assertStringNotContainsString('PENANDA-RAHASIA-123', var_export($result, true));
        $this->assertTrue(TrustedProxyList::isRejectedMetadata($result['rejected']));
    }

    public function test_rejected_metadata_validation(): void
    {
        $this->assertTrue(TrustedProxyList::isRejectedMetadata([]));
        $this->assertFalse(TrustedProxyList::isRejectedMetadata(null));
        $this->assertFalse(TrustedProxyList::isRejectedMetadata([['position' => 1]]));
        $this->assertFalse(TrustedProxyList::isRejectedMetadata([['position' => 1, 'reason' => 'nilai-mentah']]));
        $this->assertFalse(TrustedProxyList::isRejectedMetadata([['position' => -1, 'reason' => TrustedProxyList::EMPTY_SEGMENT]]));
        $this->assertFalse(TrustedProxyList::isRejectedMetadata(['a' => ['position' => 1, 'reason' => TrustedProxyList::EMPTY_SEGMENT]]));
    }

    public function test_canonical_entry_check(): void
    {
        $this->assertTrue(TrustedProxyList::isCanonicalEntry('2001:db8::/32'));
        $this->assertTrue(TrustedProxyList::isCanonicalEntry('198.51.100.10'));
        $this->assertFalse(TrustedProxyList::isCanonicalEntry('2001:DB8::/32'));
        $this->assertFalse(TrustedProxyList::isCanonicalEntry('198.51.100.10/32'));
        $this->assertFalse(TrustedProxyList::isCanonicalEntry('::ffff:198.51.100.10'));
        $this->assertFalse(TrustedProxyList::isCanonicalEntry('*'));
    }

    public static function modes(): array
    {
        return [
            'tidak disetel' => [null, 'direct', 'default'],
            'direct' => ['direct', 'direct', 'declared'],
            'proxied' => ['proxied', 'proxied', 'declared'],
            'spasi luar' => [' proxied ', 'proxied', 'declared'],
            'literal null' => ['null', 'direct', 'invalid'],
            'literal (null)' => ['(null)', 'direct', 'invalid'],
            'literal empty' => ['empty', 'direct', 'invalid'],
            'string kosong' => ['', 'direct', 'invalid'],
            'spasi saja' => ['   ', 'direct', 'invalid'],
            'kapitalisasi' => ['Direct', 'direct', 'invalid'],
            'tab' => ["\tproxied", 'direct', 'invalid'],
            'newline' => ["direct\n", 'direct', 'invalid'],
            'nilai lain' => ['trusted', 'direct', 'invalid'],
            'bukan string' => [true, 'direct', 'invalid'],
        ];
    }

    /** T-1/K1/K1-a: hanya tidak disetel = default; selain direct/proxied = invalid. */
    #[DataProvider('modes')]
    public function test_mode_resolution(mixed $raw, string $mode, string $source): void
    {
        $this->assertSame(['mode' => $mode, 'mode_source' => $source], TrustedProxyList::resolveMode($raw));
    }
}
