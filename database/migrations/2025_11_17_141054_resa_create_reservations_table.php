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
        
        
        
        
        Schema::create($this->prefix .'reservations', function (Blueprint $table) use ($default_value) {
            $table->id();
            $table->uuid('uuid')->default($default_value);
            $table->string('libelle_court', 500)->nullable();
            $table->text('libelle_long')->nullable();
           
            $table->dateTime('debut');
            $table->dateTime('fin');
            
            $table->string('couleur')->nullable();
            $table->integer('validation')->default(0);
            $table->json('data')->nullable();
           
            $table->timestamps();

           // Liaisons
           $table->foreignId('salle_id')->constrained($this->prefix .'salles')->onDelete('cascade');
           //$table->foreignId('rh_marin_id')->constrained('rh_marins')->onDelete('cascade');
           
           $table->unsignedBigInteger('rh_marin_id')->nullable();
           $table->foreign('rh_marin_id')->references('id')->on('rh_marins')->onDelete('cascade');

           // Champ Inputs pour inscription sans compte
           $table->string('email')->nullable();
           $table->string('display_name')->nullable();
           $table->string('nid', 15)->nullable();


        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->prefix .'reservations');
       
    }
};
