<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_product');
            $table->unsignedBigInteger('id_user');
            $table->enum('type', ['entry', 'exit']);
            $table->unsignedInteger('quantity');
            $table->text('note')->nullable();
            $table->string('supplier')->nullable();
            $table->string('reason')->nullable();
            $table->date('date');
            $table->timestamps();

            $table->foreign('id_product')->references('id_product')->on('products')->cascadeOnDelete();
            $table->foreign('id_user')->references('id_personne')->on('users')->cascadeOnDelete();
            $table->index(['date', 'id_product']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
