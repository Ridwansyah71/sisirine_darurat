<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('alarm_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('alarm_logs', 'target_type')) {
                $table->string('target_type')->nullable()->after('action');
            }
            if (!Schema::hasColumn('alarm_logs', 'target_id')) {
                $table->unsignedBigInteger('target_id')->nullable()->after('target_type');
            }
            if (!Schema::hasColumn('alarm_logs', 'user_name')) {
                $table->string('user_name')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('alarm_logs', 'description')) {
                $table->text('description')->nullable()->after('details');
            }
            if (!Schema::hasColumn('alarm_logs', 'old_data')) {
                $table->json('old_data')->nullable()->after('description');
            }
            if (!Schema::hasColumn('alarm_logs', 'new_data')) {
                $table->json('new_data')->nullable()->after('old_data');
            }
            if (!Schema::hasColumn('alarm_logs', 'user_agent')) {
                $table->string('user_agent')->nullable()->after('ip_address');
            }
        });
    }

    public function down()
    {
        Schema::table('alarm_logs', function (Blueprint $table) {
            $table->dropColumn([
                'target_type', 'target_id', 'user_name',
                'description', 'old_data', 'new_data', 'user_agent'
            ]);
        });
    }
};