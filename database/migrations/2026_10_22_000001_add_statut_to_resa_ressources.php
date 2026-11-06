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
            // 'disponible' (par défaut) | 'entretien' | 'indisponible' — distinct de `actif`
            // (actif = toujours au catalogue ; statut = peut être réservé maintenant ou non).
            $table->string('statut', 20)->default('disponible')->after('actif');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'ressources', function (Blueprint $table) {
            $table->dropColumn('statut');
        });
    }
};
