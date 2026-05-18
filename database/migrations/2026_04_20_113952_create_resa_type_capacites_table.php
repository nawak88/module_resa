<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Support\Facades\DB;
use Modules\Resa\Traits\HasTablePrefix;

return new class extends Migration
{
    use HasTablePrefix;

    public function up(): void
    {
        $default_value = DB::connection() instanceof PostgresConnection
            ? DB::raw('(gen_random_uuid())')
            : DB::raw('(UUID())');

        Schema::create($this->prefix . 'type_capacites', function (Blueprint $table) use ($default_value) {
            $table->id();
            $table->uuid('uuid')->default($default_value);
            $table->string('libelle_court', 500)->nullable();
            $table->text('libelle_long')->nullable();
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'type_capacites');
    }
};
