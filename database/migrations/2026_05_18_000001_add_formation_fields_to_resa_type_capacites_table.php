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
        Schema::table($this->prefix . 'type_capacites', function (Blueprint $table) {
            $table->tinyInteger('nb_users_par_poste')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'type_capacites', function (Blueprint $table) {
            $table->dropColumn(['nb_users_par_poste']);
        });
    }
};


