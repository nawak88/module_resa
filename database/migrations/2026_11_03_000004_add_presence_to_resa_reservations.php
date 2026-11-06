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
        // Pointeuse : le marin pointe lui-même sa présence (le cas courant), un
        // gestionnaire peut le faire à sa place (marin absent, oubli, etc.).
        // null = pas encore pointé, true = présent, false = absent/annulé sur place.
        Schema::table($this->prefix . 'reservations', function (Blueprint $table) {
            $table->boolean('presence')->nullable()->after('validated_at');
            $table->foreignId('presence_marquee_par')->nullable()->after('presence')->constrained('users')->nullOnDelete();
            $table->timestamp('presence_marquee_at')->nullable()->after('presence_marquee_par');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('presence_marquee_par');
            $table->dropColumn(['presence', 'presence_marquee_at']);
        });
    }
};
