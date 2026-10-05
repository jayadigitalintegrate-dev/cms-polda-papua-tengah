<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Basis security regression test.
 *
 * Pengaman: test dihentikan bila koneksi bukan SQLite in-memory, sehingga
 * suite ini tidak pernah menyentuh database MySQL lokal/production.
 */
abstract class SecurityTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() !== 'sqlite'
            || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->fail('Security tests hanya boleh berjalan pada SQLite :memory: (cek phpunit.xml / config cache).');
        }
    }
}
