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
        Schema::create($this->prefix . 'poste_capacites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salle_poste_id')
                  ->constrained($this->prefix . 'salle_postes')
                  ->cascadeOnDelete();
            $table->foreignId('type_capacite_id')
                  ->constrained($this->prefix . 'type_capacites')
                  ->restrictOnDelete();

            // Un poste ne peut pas avoir deux fois la même capacité
            $table->unique(['salle_poste_id', 'type_capacite_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'poste_capacites');
    }
};

