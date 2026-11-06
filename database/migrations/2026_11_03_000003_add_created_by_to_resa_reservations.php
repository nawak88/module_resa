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
        // user_id désigne le stagiaire/participant (pas forcément celui qui réserve) :
        // ce champ permet de retrouver "mes réservations" côté personne qui a
        // effectué la demande, même pour un tiers (cf. RessourceReservation qui a
        // déjà cette distinction via emprunteur_user_id).
        Schema::table($this->prefix . 'reservations', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
