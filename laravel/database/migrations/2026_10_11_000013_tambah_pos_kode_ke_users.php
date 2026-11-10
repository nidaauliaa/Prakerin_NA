<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Zona absen per peserta: users.pos_kode berisi 'kode' pos dari config/prakerin.php (mis. 'pos-3').
// NULL = zona belum ditentukan admin.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'pos_kode')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('pos_kode', 30)->nullable()->after('departemen_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'pos_kode')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['pos_kode']);
                $table->dropColumn('pos_kode');
            });
        }
    }
};
