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
            // Ex. "FPV Entraînement" (auto-validée) vs "FPV Examen" (validée par un
            // gestionnaire de la formation) : deux Formation distinctes, ce champ
            // pilote si la réservation est validée dès sa création ou reste en attente.
            $table->boolean('validation_auto')->default(false)->after('nb_users_par_poste');

            // Consignes envoyées dans l'email de confirmation quand la réservation est
            // auto-validée (ex. où récupérer les clés, où se trouve la salle FPS...).
            $table->text('notice')->nullable()->after('validation_auto');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'formations', function (Blueprint $table) {
            $table->dropColumn(['validation_auto', 'notice']);
        });
    }
};
