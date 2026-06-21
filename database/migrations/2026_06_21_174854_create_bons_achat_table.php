<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bons_achat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clientid');
            $table->decimal('montant', 10, 3);
            $table->date('date_emission');
            $table->date('date_expiration');
            $table->boolean('utilise')->default(false);
            $table->unsignedBigInteger('ticketid_source')->nullable();
            $table->timestamps();
            
            // Note: clientid doesn't necessarily have a foreign key to "clients" because the DB 
            // uses raw IDs without strict FK constraints in some legacy setups, 
            // but we can add it if we want.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bons_achat');
    }
};
