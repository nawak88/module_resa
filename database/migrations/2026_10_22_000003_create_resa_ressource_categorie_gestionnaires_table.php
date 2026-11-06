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
        Schema::create($this->prefix . 'ressource_categorie_gestionnaires', function (Blueprint $table) {
            $table->unsignedBigInteger('ressource_categorie_id');
            $table->unsignedBigInteger('user_id');
            $table->primary(['ressource_categorie_id', 'user_id']);
            $table->foreign('ressource_categorie_id', 'resa_rc_gestionnaires_categorie_fk')
                ->references('id')->on($this->prefix . 'ressource_categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'ressource_categorie_gestionnaires');
    }
};
