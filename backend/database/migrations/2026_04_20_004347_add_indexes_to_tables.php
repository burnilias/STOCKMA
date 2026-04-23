<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('company_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('company_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('company_id');
            $table->index('nom');
            $table->index('id_categorie');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index('company_id');
            $table->index('type');
        });

        Schema::table('alerte_stocks', function (Blueprint $table) {
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::table('alerte_stocks', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
            $table->dropIndex(['type']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
            $table->dropIndex(['nom']);
            $table->dropIndex(['id_categorie']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
        });
    }
};
