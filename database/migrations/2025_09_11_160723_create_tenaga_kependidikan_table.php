<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tenaga_kependidikan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('jabatan');
            $table->text('alamat')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
});

    }

    public function down(): void {
        Schema::dropIfExists('tenaga_kependidikan');
    }
};
