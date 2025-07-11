<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cleaning_schedules', function (Blueprint $table) {
            $table->dateTime('started_at')->nullable()->after('scheduled_at');
            $table->integer('cleaning_duration')->nullable()->after('completed_at');
        });
    }

    public function down()
    {
        Schema::table('cleaning_schedules', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'cleaning_duration']);
        });
    }
};
