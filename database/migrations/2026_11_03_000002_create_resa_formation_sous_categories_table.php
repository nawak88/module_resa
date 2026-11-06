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
        // Sous-catégories d'une formation (ex. FPV → "Entraînement" / "Examen") : chacune
        // porte sa propre règle de validation. 0 ou 1 sous-catégorie = la formation se
        // comporte comme avant, directement avec ses propres validation_auto/notice ;
        // 2+ sous-catégories = le réservataire doit en choisir une, qui prend le dessus.
        Schema::create($this->prefix . 'formation_sous_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained($this->prefix . 'formations')->cascadeOnDelete();
            $table->string('libelle_court', 255);
            $table->boolean('validation_auto')->default(false);
            $table->text('notice')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();
        });

        Schema::table($this->prefix . 'reservations', function (Blueprint $table) {
            $table->foreignId('sous_categorie_id')
                ->nullable()
                ->after('formations_utilisees')
                ->constrained($this->prefix . 'formation_sous_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sous_categorie_id');
        });

        Schema::dropIfExists($this->prefix . 'formation_sous_categories');
    }
};
