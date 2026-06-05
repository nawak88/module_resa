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
        Schema::table($this->prefix . 'type_salles', function (Blueprint $table) {
            $table->string('icone', 100)->nullable()->after('ordre');
            $table->string('couleur', 50)->nullable()->after('icone');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'type_salles', function (Blueprint $table) {
            $table->dropColumn(['icone', 'couleur']);
        });
    }
};
