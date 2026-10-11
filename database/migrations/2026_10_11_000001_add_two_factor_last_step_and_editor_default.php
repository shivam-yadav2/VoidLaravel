<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Last TOTP time step accepted at login, so the same 6-digit code
            // can't be replayed inside its validity window.
            $table->unsignedBigInteger('two_factor_last_step')->nullable()->after('two_factor_confirmed_at');

            // Least privilege for any row created without an explicit role
            // (factories, ad-hoc inserts). Existing rows keep their role.
            $table->string('role')->default('editor')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('two_factor_last_step');
            $table->string('role')->default('admin')->change();
        });
    }
};
