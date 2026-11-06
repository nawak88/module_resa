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
            // Nullable en base (ne casse pas les réservations existantes) ; obligatoires
            // côté formulaire pour toute nouvelle réservation (RessourceGanttWidget).
            $table->string('batiment')->nullable()->after('libelle');
            $table->string('service')->nullable()->after('batiment');
            $table->foreignId('emprunteur_user_id')->nullable()->after('service')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('officier_responsable_user_id')->nullable()->after('emprunteur_user_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'ressource_reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('emprunteur_user_id');
            $table->dropConstrainedForeignId('officier_responsable_user_id');
            $table->dropColumn(['batiment', 'service']);
        });
    }
};
