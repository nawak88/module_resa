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
        Schema::table($this->prefix . 'ressources', function (Blueprint $table) {
            $table->foreignId('unite_id')->nullable()->after('ressource_categorie_id')
                ->constrained('rh_unites')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'ressources', function (Blueprint $table) {
            $table->dropForeignIdFor('unite_id', 'rh_unites');
            $table->dropColumn('unite_id');
        });
    }
};
