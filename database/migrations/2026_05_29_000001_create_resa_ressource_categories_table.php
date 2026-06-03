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
        Schema::create($this->prefix . 'ressource_categories', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('libelle_court')->nullable();
            $table->string('couleur')->default('primary');
            $table->string('icone')->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'ressource_categories');
    }
};
