<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->string('name', 255)->change();
            $table->string('floor_location', 100)->nullable()->change();

            if (!Schema::hasColumn('facilities', 'source_origin')) {
                $table->string('source_origin', 100)->nullable()->after('name');
            }
        });

        // Mengakomodasi enum ruang_kerja secara langsung via native SQL MySQL
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE facilities MODIFY COLUMN category ENUM('auditorium', 'lab', 'kelas', 'olahraga', 'rapat', 'ruang_kerja') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            if (Schema::hasColumn('facilities', 'source_origin')) {
                $table->dropColumn('source_origin');
            }
            $table->string('name', 150)->change();
            $table->string('floor_location', 50)->nullable()->change();
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE facilities MODIFY COLUMN category ENUM('auditorium', 'lab', 'kelas', 'olahraga', 'rapat') NOT NULL");
        }
    }
};
