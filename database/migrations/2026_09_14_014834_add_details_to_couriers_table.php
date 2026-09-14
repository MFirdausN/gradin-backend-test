<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('couriers', function (Blueprint $table) {
            $table->string('name')->index();
            $table->string('phone', 20)->unique();
            $table->string('email')->nullable()->unique();
            $table->unsignedTinyInteger('level')->default(1)->index();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('couriers', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropUnique(['phone']);
            $table->dropUnique(['email']);
            $table->dropIndex(['level']);
            $table->dropIndex(['created_at']);
            $table->dropColumn(['name', 'phone', 'email', 'level', 'is_active', 'deleted_at']);
        });
    }
};
