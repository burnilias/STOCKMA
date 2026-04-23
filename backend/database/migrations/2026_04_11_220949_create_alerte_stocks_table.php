<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * AlerteStock (UML: id_alerte, seuil_min, date_alerte, id_product).
     */
    public function up(): void
    {
        Schema::create('alerte_stocks', function (Blueprint $table) {
            $table->bigIncrements('id_alerte');
            $table->unsignedInteger('seuil_min');
            $table->date('date_alerte');
            $table->unsignedBigInteger('id_product');
            $table->timestamps();

            $table->foreign('id_product')->references('id_product')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerte_stocks');
    }
};
