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
            $table->string('phone', 20)->nullable()->unique()->after('email');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->string('avatar')->nullable()->after('password');
            $table->char('locale', 2)->default('bn')->after('avatar');
            $table->unsignedInteger('loyalty_points')->default(0)->after('locale');
            $table->string('referral_code', 20)->nullable()->unique()->after('loyalty_points');
            $table->foreignId('referred_by')->nullable()->after('referral_code')
                ->constrained('users')->nullOnDelete();
            $table->text('two_factor_secret')->nullable()->after('remember_token');
            $table->timestamp('two_factor_enabled_at')->nullable()->after('two_factor_secret');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by');
            $table->dropColumn([
                'phone', 'phone_verified_at', 'avatar', 'locale',
                'loyalty_points', 'referral_code',
                'two_factor_secret', 'two_factor_enabled_at',
            ]);
        });
    }
};
