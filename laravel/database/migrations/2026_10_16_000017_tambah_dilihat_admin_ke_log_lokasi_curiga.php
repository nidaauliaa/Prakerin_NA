<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Penanda "sudah dilihat admin" supaya angka merah di menu Abnormal GPS hilang setelah halamannya dibuka.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('log_lokasi_curiga') && ! Schema::hasColumn('log_lokasi_curiga', 'dilihat_admin_at')) {
            Schema::table('log_lokasi_curiga', function (Blueprint $table) {
                $table->timestamp('dilihat_admin_at')->nullable()->after('user_agent');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('log_lokasi_curiga', 'dilihat_admin_at')) {
            Schema::table('log_lokasi_curiga', function (Blueprint $table) {
                $table->dropColumn('dilihat_admin_at');
            });
        }
    }
};
