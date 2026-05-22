<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Resa\Traits\HasTablePrefix;

return new class extends Migration
{
    use HasTablePrefix;

    public function up(): void
    {
        Schema::table($this->prefix . 'formations', function (Blueprint $table) {
            $table->string('couleur')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'formations', function (Blueprint $table) {
            $table->dropColumn('couleur');
        });
    }
};
