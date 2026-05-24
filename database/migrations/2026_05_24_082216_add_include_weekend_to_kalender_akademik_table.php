<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('kalender_akademik', function (Blueprint $table) {
        $table->boolean('include_weekend')->default(false)->after('tanggal_selesai');
    });
}

public function down(): void
{
    Schema::table('kalender_akademik', function (Blueprint $table) {
        $table->dropColumn('include_weekend');
    });
}
};
