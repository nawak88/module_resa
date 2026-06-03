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
        Schema::create($this->prefix . 'ressources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ressource_categorie_id')->constrained($this->prefix . 'ressource_categories')->cascadeOnDelete();
            $table->string('libelle');
            $table->string('libelle_court')->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'ressources');
    }
};
