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
            $table->string('email', 500)->nullable()->after('nb_users_par_poste');
        });

        Schema::create($this->prefix . 'formation_gestionnaires', function (Blueprint $table) {
            $table->foreignId('formation_id')->constrained($this->prefix . 'formations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['formation_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'formation_gestionnaires');
        Schema::table($this->prefix . 'formations', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
