<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add OTP fields
            $table->string('reset_otp')->nullable()->after('password');
            $table->dateTime('reset_otp_expires_at')->nullable()->after('reset_otp');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Rollback changes
            $table->dropColumn('reset_otp');
            $table->dropColumn('reset_otp_expires_at');
        });
    }
};
