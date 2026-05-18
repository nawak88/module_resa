<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Support\Facades\DB;
use Modules\Resa\Traits\HasTablePrefix;

return new class extends Migration
{
    use HasTablePrefix;

    public function up(): void
    {
        $default_value = DB::connection() instanceof PostgresConnection
            ? DB::raw('(gen_random_uuid())')
            : DB::raw('(UUID())');

        Schema::create($this->prefix . 'reservations', function (Blueprint $table) use ($default_value) {
            $table->id();
            $table->uuid('uuid')->default($default_value);
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->foreignId('salle_poste_id')
                  ->constrained($this->prefix . 'salle_postes')
                  ->restrictOnDelete();
            $table->string('libelle_court', 500)->nullable();
            $table->text('libelle_long')->nullable();
            $table->dateTime('debut');
            $table->dateTime('fin');
            $table->string('couleur')->nullable();
            $table->integer('validation')->default(0)->comment('0 = en attente, 1 = validé');
            $table->json('capacites_utilisees')->nullable()->comment('IDs TypeCapacite choisis par le user');
            $table->foreignId('validated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->dateTime('validated_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();

            // Vérification chevauchement
            $table->index(['salle_poste_id', 'debut', 'fin']);
            // Filtrage par user et statut
            $table->index(['user_id', 'validation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'reservations');
    }
};
