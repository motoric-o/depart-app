<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add Driver Role
        DB::table('account_types')->insertOrIgnore(['name' => 'Driver']);

        // 2. Add driver_id and remarks to schedules
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('driver_id')->nullable()->after('bus_id');
            $table->text('remarks')->nullable()->after('status');
            $table->foreign('driver_id')->references('id')->on('accounts')->onDelete('set null');
        });

        // 3. Add is_checked_in to bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('is_checked_in')->default(false)->after('status');
        });

        // 4. Add status to expenses
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('status')->default('Approved')->after('amount'); // Approved, Pending, Rejected
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropColumn(['driver_id', 'remarks']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('is_checked_in');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
