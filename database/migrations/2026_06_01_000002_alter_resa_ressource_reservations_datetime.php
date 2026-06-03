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
        Schema::table($this->prefix . 'ressource_reservations', function (Blueprint $table) {
            $table->dateTime('debut')->change();
            $table->dateTime('fin')->change();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'ressource_reservations', function (Blueprint $table) {
            $table->date('debut')->change();
            $table->date('fin')->change();
        });
    }
};
