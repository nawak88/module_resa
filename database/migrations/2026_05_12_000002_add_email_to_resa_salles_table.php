<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resa_salles', function (Blueprint $table) {
            $table->string('email')->nullable()->after('unite_id');
        });
    }

    public function down(): void
    {
        Schema::table('resa_salles', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
