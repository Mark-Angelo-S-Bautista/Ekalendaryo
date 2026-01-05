<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('password');
            $table->timestamp('deleted_at')->nullable()->after('is_deleted');
            $table->string('deleted_school_year')->nullable()->after('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_deleted',
                'deleted_at',
                'deleted_school_year'
            ]);
        });
    }
};
