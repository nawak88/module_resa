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
        Schema::table($this->prefix . 'type_postes', function (Blueprint $table) {
            $table->boolean('system')->default(false)->after('ordre')
                  ->comment('Si true : entree systeme non supprimable');
        });

        Schema::table($this->prefix . 'salle_postes', function (Blueprint $table) {
            $table->boolean('system')->default(false)->after('ordre')
                  ->comment('Si true : poste systeme non supprimable (ex: Salle entiere)');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'type_postes', function (Blueprint $table) {
            $table->dropColumn('system');
        });

        Schema::table($this->prefix . 'salle_postes', function (Blueprint $table) {
            $table->dropColumn('system');
        });
    }
};
