<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->rebuild([
            'diterima',
            'diverifikasi',
            'diproses',
            'dikerjakan',
            'selesai',
            'ditolak',
        ]);
    }

    public function down(): void
    {
        if (DB::table('complaints')
            ->where('status', 'dikerjakan')
            ->exists()) {
            throw new RuntimeException(
                'Rollback ditolak: masih ada pengaduan berstatus dikerjakan.'
            );
        }

        $this->rebuild([
            'diterima',
            'diverifikasi',
            'diproses',
            'selesai',
            'ditolak',
        ]);
    }

    private function rebuild(array $statuses): void
    {
        $db = DB::connection();

        if ($db->getDriverName() !== 'sqlite') {
            throw new RuntimeException('Migration khusus SQLite.');
        }

        if ((int) $db->scalar('PRAGMA foreign_keys') !== 1) {
            throw new RuntimeException('Foreign key harus aktif.');
        }

        $tables = $db->select(
            "SELECT name FROM sqlite_master WHERE type = 'table'"
        );

        foreach ($tables as $table) {
            $quoted = '"' . str_replace('"', '""', $table->name) . '"';

            foreach ($db->select("PRAGMA foreign_key_list($quoted)") as $fk) {
                if (strcasecmp($fk->table, 'complaints') === 0) {
                    throw new RuntimeException(
                        'Migration ditolak: terdapat foreign key masuk.'
                    );
                }
            }
        }

        $triggers = $db->select(
            "SELECT name FROM sqlite_master
             WHERE type = 'trigger'
             AND (tbl_name = 'complaints'
                  OR sql LIKE '%complaints%')"
        );

        if (count($triggers) > 0) {
            throw new RuntimeException(
                'Migration ditolak: trigger terkait complaints ditemukan.'
            );
        }

        $db->transaction(function () use ($db, $statuses): void {
            $temporary = 'complaints_status_rebuild';

            if ($db->getSchemaBuilder()->hasTable($temporary)) {
                throw new RuntimeException(
                    'Tabel sementara sudah ada.'
                );
            }

            $before = $db->table('complaints')->count();

            $allowed = implode(', ', array_map(
                fn (string $status): string => "'" . $status . "'",
                $statuses
            ));

            $db->statement("
                CREATE TABLE complaints_status_rebuild (
                    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    nama VARCHAR NOT NULL,
                    ktp VARCHAR NOT NULL,
                    hp VARCHAR NOT NULL,
                    email VARCHAR NOT NULL,
                    alamat TEXT NOT NULL,
                    jenis VARCHAR NOT NULL,
                    isi TEXT NOT NULL,
                    tiket VARCHAR NOT NULL,
                    status VARCHAR NOT NULL DEFAULT 'diterima'
                        CHECK (status IN ($allowed)),
                    catatan_admin TEXT,
                    processed_by INTEGER,
                    processed_at DATETIME,
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY (processed_by)
                        REFERENCES users(id) ON DELETE SET NULL
                )
            ");

            $columns = implode(', ', [
                'id', 'nama', 'ktp', 'hp', 'email',
                'alamat', 'jenis', 'isi', 'tiket',
                'status', 'catatan_admin', 'processed_by',
                'processed_at', 'created_at', 'updated_at',
            ]);

            $db->statement("
                INSERT INTO complaints_status_rebuild ($columns)
                SELECT $columns FROM complaints
            ");

            $copied = $db->table($temporary)->count();

            if ($copied !== $before) {
                throw new RuntimeException(
                    'Jumlah laporan berubah saat penyalinan.'
                );
            }

            $violations = $db->select(
                'PRAGMA foreign_key_check(complaints_status_rebuild)'
            );

            if (count($violations) !== 0) {
                throw new RuntimeException(
                    'Foreign key check gagal.'
                );
            }

            $db->statement('DROP TABLE complaints');
            $db->statement(
                'ALTER TABLE complaints_status_rebuild RENAME TO complaints'
            );

            $db->statement(
                'CREATE UNIQUE INDEX complaints_tiket_unique
                 ON complaints(tiket)'
            );

            foreach ([
                'created_at',
                'email',
                'jenis',
                'status',
            ] as $column) {
                $db->statement(
                    "CREATE INDEX complaints_{$column}_index
                     ON complaints($column)"
                );
            }

            if ($db->table('complaints')->count() !== $before) {
                throw new RuntimeException(
                    'Jumlah laporan berubah setelah rebuild.'
                );
            }

            if (count($db->select('PRAGMA foreign_key_check')) !== 0) {
                throw new RuntimeException(
                    'Pemeriksaan relasi akhir gagal.'
                );
            }
        });
    }
};
