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

    
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        
        if (DB::connection() instanceof PostgresConnection) {
            $default_value = DB::raw('(gen_random_uuid())');
        } elseif (DB::connection() instanceof MySqlConnection) {
            $default_value = DB::raw('(UUID())');
        }
        
        Schema::create($this->prefix .'salles', function (Blueprint $table)  use ($default_value) {
            $table->id();
            $table->uuid('uuid')->default($default_value);
            $table->string('code')->nullable();
            $table->string('libelle_court', 500)->nullable();
            $table->text('libelle_long')->nullable();
            $table->foreignId('type_salle_id')
                  ->constrained($this->prefix .'type_salles')
                  ->restrictOnDelete();
            $table->integer('capacite')->nullable();
            $table->string('image')->nullable(); 
            $table->string('color')->nullable();
            $table->integer('ordre')->default(0);
            $table->json('data')->nullable();
            $table->timestamps();
        });
        
        
        

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       
        Schema::dropIfExists($this->prefix .'salles');
    }
};
