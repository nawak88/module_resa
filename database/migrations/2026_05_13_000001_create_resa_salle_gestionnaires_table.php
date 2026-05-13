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
        Schema::create($this->prefix . 'salle_gestionnaires', function (Blueprint $table) {
            $table->unsignedBigInteger('salle_id');
            $table->unsignedBigInteger('user_id');
            $table->primary(['salle_id', 'user_id']);
            $table->foreign('salle_id')->references('id')->on($this->prefix . 'salles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'salle_gestionnaires');
    }
};
