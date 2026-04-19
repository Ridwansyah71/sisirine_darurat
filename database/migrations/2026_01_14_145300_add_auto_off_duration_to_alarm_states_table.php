<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom sudah ada di create table, skip
        if (!Schema::hasColumn('alarm_states', 'auto_off_duration')) {
            Schema::table('alarm_states', function (Blueprint $table) {
                $table->integer('auto_off_duration')->default(60);
            });
        }
    }

    public function down(): void
    {
        // tidak perlu drop karena sudah ada sejak create
    }
};